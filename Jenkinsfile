pipeline {
    agent any
    stages {
        stage('Build') {
            steps {
                echo 'Build completed'
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
                    echo 'Skipping branch-specific deployment for branch:', currentBranch
                }
            }
        }

        failure {
            echo 'Build failed! Sending email notification...'
            // Send email notification with logs
            emailext body: 'Build failed. See Jenkins console output for details.\\n\\n${BUILD_URL}', subject: 'Build Failed', to: 'jorrit.vanderheide001@gmail.com'
        }
    }
}
