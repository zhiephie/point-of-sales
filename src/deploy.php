<?php
namespace Deployer;

require 'recipe/laravel.php';
require 'recipe/rsync.php';

// Project name
set('application', 'point-of-sales');

// Project repository
set('repository', 'https://github.com/zhiephie/point-of-sales.git');

// [Optional] Allocate tty for git clone. Default value is false.
set('git_tty', true);

set('rsync_src', function () {
    return __DIR__; // If your project isn't in the root, you'll need to change this.
});

// Configuring the rsync exclusions.
// You'll want to exclude anything that you don't want on the production server.
add('rsync', [
    'exclude' => [
        '.git',
        '/.env',
        '/storage/',
        '/vendor/',
        '/node_modules/',
        '.github',
        'deploy.php',
    ],
]);

// Set up a deployer task to copy secrets to the server.
// Grabs the dotenv file from the github secret
task('deploy:secrets', function () {
    file_put_contents(__DIR__ . '/.env', getenv('DOT_ENV'));
    upload('.env', get('deploy_path') . '/shared');
});

// Hosts
host('pos.timexstudio.com') // Name of the server
    ->hostname(getenv('SSH_HOST')) // Hostname or IP address
    ->port(getenv('SSH_PORT')) // SSH port
    ->stage('production') // Deployment stage (production, staging, etc)
    ->user(getenv('SSH_USER')) // SSH user
    ->set('deploy_path', getenv('DEPLOY_PATH')) // Deploy path
    ->set('http_user', 'nginx');

// Tasks

desc('Deploy the application');
task('deploy', [
    'deploy:info',
    'deploy:prepare',
    'deploy:lock',
    'deploy:release',
    'rsync', // Deploy code & built assets
    'deploy:secrets', // Deploy secrets
    'deploy:shared',
    'deploy:vendors',
    'deploy:writable',
    'artisan:optimize',     // |
    'artisan:migrate',      // |
    'deploy:symlink',
    'deploy:unlock',
    'cleanup',
]);

// [Optional] if deploy fails automatically unlock.
after('deploy:failed', 'deploy:unlock');
