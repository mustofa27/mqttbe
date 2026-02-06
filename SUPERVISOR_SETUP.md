# Supervisor Setup Guide for ICMQTT

This guide will help you set up Supervisor to manage Laravel queue workers and the scheduler for the ICMQTT application.

## Prerequisites

- VPS with Ubuntu/Debian or CentOS/RHEL
- Laravel application deployed on VPS
- Root or sudo access

## 1. Install Supervisor

### For Ubuntu/Debian:
```bash
sudo apt update
sudo apt install supervisor -y
```

### For CentOS/RHEL:
```bash
sudo yum install epel-release -y
sudo yum install supervisor -y
```

### Start and Enable Supervisor
```bash
sudo systemctl start supervisor
sudo systemctl enable supervisor
sudo systemctl status supervisor
```

## 2. Configure Supervisor for ICMQTT

### Step 1: Edit the Configuration File

Open the `supervisor-icmqtt.conf` file in this project and update the following:

1. **Replace all `/path/to/your/MqttBE`** with your actual VPS path (e.g., `/var/www/icmqtt`)
2. **Update the `user`** to your VPS user (e.g., `ubuntu`, `www-data`, or your username)
3. **Update PHP path** if needed (use `which php` to find it)

Example configuration after updates:
```ini
[program:icmqtt-worker]
process_name=%(program_name)s_%(process_num)02d
command=/usr/bin/php /var/www/icmqtt/artisan queue:work database --sleep=3 --tries=3 --max-time=3600
autostart=true
autorestart=true
stopasgroup=true
killasgroup=true
user=www-data
numprocs=2
redirect_stderr=true
stdout_logfile=/var/www/icmqtt/storage/logs/worker.log
stopwaitsecs=3600

[program:icmqtt-scheduler]
process_name=%(program_name)s
command=/usr/bin/php /var/www/icmqtt/artisan schedule:work
autostart=true
autorestart=true
stopasgroup=true
killasgroup=true
user=www-data
numprocs=1
redirect_stderr=true
stdout_logfile=/var/www/icmqtt/storage/logs/scheduler.log
```

### Step 2: Copy Configuration to Supervisor Directory

```bash
sudo cp supervisor-icmqtt.conf /etc/supervisor/conf.d/icmqtt.conf
```

### Step 3: Create Log Files (if they don't exist)

```bash
touch /var/www/icmqtt/storage/logs/worker.log
touch /var/www/icmqtt/storage/logs/scheduler.log
chmod 664 /var/www/icmqtt/storage/logs/*.log
```

## 3. Start Supervisor Processes

### Reload Supervisor Configuration
```bash
sudo supervisorctl reread
sudo supervisorctl update
```

### Start the Processes
```bash
sudo supervisorctl start icmqtt-worker:*
sudo supervisorctl start icmqtt-scheduler:
```

### Check Status
```bash
sudo supervisorctl status
```

You should see output like:
```
icmqtt-scheduler:icmqtt-scheduler    RUNNING   pid 12345, uptime 0:00:10
icmqtt-worker:icmqtt-worker_00       RUNNING   pid 12346, uptime 0:00:10
icmqtt-worker:icmqtt-worker_01       RUNNING   pid 12347, uptime 0:00:10
```

## 4. Managing Supervisor Processes

### Stop All ICMQTT Processes
```bash
sudo supervisorctl stop icmqtt-worker:*
sudo supervisorctl stop icmqtt-scheduler:
```

### Restart All ICMQTT Processes
```bash
sudo supervisorctl restart icmqtt-worker:*
sudo supervisorctl restart icmqtt-scheduler:
```

### Stop Individual Worker
```bash
sudo supervisorctl stop icmqtt-worker:icmqtt-worker_00
```

### View Process Logs
```bash
sudo supervisorctl tail icmqtt-worker:icmqtt-worker_00
sudo supervisorctl tail -f icmqtt-scheduler  # Follow mode
```

## 5. What Each Process Does

### Queue Worker (`icmqtt-worker`)
- Processes background jobs from the `database` queue
- Handles payment webhook processing
- Sends email notifications (password reset, payment confirmations)
- Runs 2 parallel workers for better performance
- Auto-restarts every hour (`--max-time=3600`)

### Scheduler (`icmqtt-scheduler`)
- Runs Laravel's task scheduler continuously
- Executes daily subscription expiry checks
- Downgrades expired subscriptions to free tier
- Runs other scheduled tasks defined in `routes/console.php`

## 6. Troubleshooting

### Check if Supervisor is Running
```bash
sudo systemctl status supervisor
```

### View All Logs
```bash
tail -f /var/www/icmqtt/storage/logs/worker.log
tail -f /var/www/icmqtt/storage/logs/scheduler.log
tail -f /var/log/supervisor/supervisord.log
```

### Process Keeps Failing
1. Check file permissions:
   ```bash
   sudo chown -R www-data:www-data /var/www/icmqtt
   sudo chmod -R 755 /var/www/icmqtt
   sudo chmod -R 775 /var/www/icmqtt/storage
   sudo chmod -R 775 /var/www/icmqtt/bootstrap/cache
   ```

2. Verify PHP path:
   ```bash
   which php
   ```

3. Test artisan command manually:
   ```bash
   cd /var/www/icmqtt
   php artisan queue:work database --once
   php artisan schedule:list
   ```

### Remove Configuration
```bash
sudo rm /etc/supervisor/conf.d/icmqtt.conf
sudo supervisorctl reread
sudo supervisorctl update
```

## 7. After Code Updates

When you deploy new code to your VPS:

```bash
# Stop workers
sudo supervisorctl stop icmqtt-worker:*

# Update your code (git pull, composer install, etc.)
cd /var/www/icmqtt
git pull
composer install --no-dev --optimize-autoloader

# Run migrations
php artisan migrate --force

# Clear caches
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Restart workers
sudo supervisorctl start icmqtt-worker:*
sudo supervisorctl restart icmqtt-scheduler:
```

## 8. Monitoring

### Check Queue Status
```bash
php artisan queue:monitor database
```

### View Failed Jobs
```bash
php artisan queue:failed
```

### Retry Failed Jobs
```bash
php artisan queue:retry all
```

## 9. Performance Tuning

### Increase Worker Count
Edit `/etc/supervisor/conf.d/icmqtt.conf` and change:
```ini
numprocs=4  # Increase from 2 to 4
```

Then reload:
```bash
sudo supervisorctl reread
sudo supervisorctl update
```

### Adjust Memory Limits
Add `--memory=128` to limit memory usage:
```ini
command=/usr/bin/php /var/www/icmqtt/artisan queue:work database --sleep=3 --tries=3 --max-time=3600 --memory=128
```

## Support

For issues, check:
- `/var/www/icmqtt/storage/logs/laravel.log` - Application logs
- `/var/www/icmqtt/storage/logs/worker.log` - Queue worker logs
- `/var/www/icmqtt/storage/logs/scheduler.log` - Scheduler logs
- `/var/log/supervisor/supervisord.log` - Supervisor logs
