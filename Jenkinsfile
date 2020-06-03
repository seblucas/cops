#!/usr/bin/env

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
        sh "sonar-scanner -Dsonar.projectKey=cops -Dsonar.sources=. -Dsonar.host.url=http://localhost:9009 -Dsonar.login=12e6b0c2af14d7db285bdb5416e10aa259580cd4"
    }
}