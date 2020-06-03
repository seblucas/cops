#!/usr/bin/env

environment {
    scannerHome = tool 'SonarQubeScanner'
}

node {

    stage("checkout"){
        checkout scm
    }

    stage("composer_install") {
        sh 'composer install'
    }

    stage("php_lint") {
        sh 'find . -name "*.php" -not -path "./vendor/*" -print0 | xargs -0 -n1 php -l'
    }

    stage("phpunit") {
        sh 'vendor/bin/phpunit'
    }

//     stage("codeception") {
//         sh 'vendor/bin/codecept run'
//     }

    stage('Sonarqube') {
        withSonarQubeEnv('sonarqube') {
            sh "${scannerHome}/bin/sonar-scanner"
        }
        timeout(time: 10, unit: 'MINUTES') {
            waitForQualityGate abortPipeline: true
        }
    }
}