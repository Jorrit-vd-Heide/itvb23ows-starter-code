pipeline {
    agent any

    environment {
        PHP_IMAGE = 'php:7.2'
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
                    docker.image(PHP_IMAGE).inside('-v $PWD:/app') {
                        'docker-php-ext-install mysqli'
                        'composer install --no-scripts --no-progress --no-suggest'
                        'phpunit'
                    }
                }
            }
        }

        stage('Run PHP Server') {
            steps {
                script {
                    docker.image(PHP_IMAGE).inside('-p 8000:8000 -v $PWD:/app') {
                        'php -S 0.0.0.0:8000 -t /app &'
                        waitUntil { script.sh(script: 'curl -s http://localhost:8000', returnStatus: true) == 0 }
                        echo 'PHP server is running successfully.'
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
                    'rm -rf vendor'  // Voorbeeld: Verwijder de "vendor" map na de build
                    'killall -9 php'  // Stop alle PHP-processen
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
