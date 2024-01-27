pipeline {
    agent any
    stages {
        stage('Build') {
            steps {
                echo 'Build completed'
            }
        }
        stage('SonarQube') {
            steps {
                script { scannerHome = tool 'SonarQube Scanner' }
                withSonarQubeEnv('SonarQube') {
                 sh "${scannerHome}/bin/sonar-scanner
                    -Dsonar.projectKey=[OWS-Hive]"
                }
            }
        }
    }

    post {
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
                    echo "Skipping branch-specific deployment for branch: ${currentBranch}"
                }
            }
        }

        failure {
            echo 'Build failed!'
            // Display failed logs
            script {
                def failedLogs = readFile("${JENKINS_HOME}/workspace/${JOB_NAME}/builds/${BUILD_NUMBER}/log")
                echo "Failed Logs:\n${failedLogs}"
            }
        }
    }
}
