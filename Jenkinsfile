pipeline {
    agent any

    environment {
        PHP_IMAGE = 'php:7.2'
        MYSQL_IMAGE = 'mysql:8.0.3'
    }

    options {
        buildDiscarder(logRotator(artifactNumToKeepStr: '5', numToKeepStr: '5'))
        timestamps()
        skipDefaultCheckout()
    }

    triggers {
        pollSCM('H/5 * * * *') // Every 5 minuts
    }

    stages {
        stage('Checkout') {
            steps {
                checkout scm
            }
        }

        stage('Build and Test') {
            steps {
                script {
                    try{
                         sh 'DOCKER_TLS_VERIFY= docker pull php:7.2'
                         sh 'DOCKER_TLS_VERIFY= docker inspect -f . php:7.2'

                         docker.withRegistry('https://registry.hub.docker.com', 'Docker-Hub') {
                            docker.image(PHP_IMAGE).inside('-v $PWD:/app') {
                                sh 'docker-php-ext-install mysqli'
                                sh 'composer install --no-scripts --no-progress --no-suggest'
                                sh 'phpunit'
                            }
                    }
                    catch (Exception e) {
                        currentBuild.result = 'FAILURE'
                        error("Build and Test failed: ${e.message}")
                    }
                }
            }
        }

        stage('Run PHP Server') {
            steps {
                script {
                    try{
                       docker.withRegistry('https://registry.hub.docker.com', 'Docker-Hub') {
                            docker.image(PHP_IMAGE).inside('-p 8000:8000 -v $PWD:/app') {
                                'php -S 0.0.0.0:8000 -t /app &'
                                waitUntil { script.sh(script: 'curl -s http://localhost:8000', returnStatus: true) == 0 }
                                echo 'PHP server is running successfully.'
                            }
                    }
                    catch (Exception e) {
                        currentBuild.result = 'FAILURE'
                        error("Build and Test failed: ${e.message}")
                    }
                }
            }
        }
    }

    post {
        always {
            // Cleanup actions
            echo 'Cleaning up...'
            // Add cleanup steps here
            script {
                docker.image(PHP_IMAGE).inside('-v $PWD:/app') {
                    sh 'rm -rf vendor'  
                    sh 'killall -9 php'  
                }
            }
        }

        success {
            echo 'Build succeeded! Deploying to develop...'
            // Deployment steps (e.g., push to a Git branch)
            script {
                    def currentBranch = sh(script: 'git rev-parse --abbrev-ref HEAD', returnStdout: true).trim()

                    if (currentBranch.startsWith('Features/')) {
                        echo "Pushing changes to features branch: ${currentBranch}"
                        sh "git push origin ${currentBranch}"
                    } else if (currentBranch.startsWith('Reworks/')) {
                        echo "Pushing changes to reworks branch: ${currentBranch}"
                        sh "git push origin ${currentBranch}"
                    } else if (currentBranch == 'Develop') {
                        echo 'Merging changes to develop...'
                        sh 'git checkout Develop'
                        sh 'git merge --no-ff master'
                        sh 'git push origin Develop'
                    } else {
                        echo 'Skipping branch-specific deployment for branch:', currentBranch
                    }
                }
        }

        failure {
            echo 'Build failed! Sending email notification...'
            // Send email notification with logs
            emailext subject: 'Build Failed',
                      body: "Build failed. See Jenkins console output for details.\n\n${BUILD_URL}",
                      to: 'jorrit.vanderheide001@gmail.com',
                      mimeType: 'text/plain'
        }
    }
}
