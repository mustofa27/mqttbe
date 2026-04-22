# Supervisor Setup Guide for ICMQTT

This guide will help you set up Supervisor for queue workers and the scheduler, plus systemd for restoring per-user MQTT subscriber processes after reboot.

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

; MQTT subscribers are no longer managed here as one shared process.
; Per-user listeners are restored after reboot through systemd using:
;     php artisan mqtt:listeners:restore
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

## 4. Configure systemd for MQTT Subscriber Restore

Per-user MQTT subscribers are started from the application with user-specific credentials and device IDs. Because of that, they should not run as one shared Supervisor program. Instead, restore them on boot with systemd.

### Step 1: Review the systemd unit template

This project includes:

```bash
deploy/systemd/mqtt-listeners-restore.service
```

Update these values inside the service file before installing it:

1. `User`
2. `Group`
3. `WorkingDirectory`
4. `ExecStart` PHP path and project path
5. log output path if your deployment path differs

### Step 2: Copy the unit file into systemd

```bash
sudo cp deploy/systemd/mqtt-listeners-restore.service /etc/systemd/system/mqtt-listeners-restore.service
```

### Step 3: Reload systemd and enable restore on boot

```bash
sudo systemctl daemon-reload
sudo systemctl enable mqtt-listeners-restore.service
```

### Step 4: Test it now

```bash
sudo systemctl start mqtt-listeners-restore.service
sudo systemctl status mqtt-listeners-restore.service
```

### Step 5: Check restore logs

```bash
tail -f /var/www/icmqtt/storage/logs/mqtt-listeners-restore.log
```

What this service does:

1. runs `php artisan mqtt:listeners:restore` once after boot
2. reads saved per-user listener metadata
3. restores only users who still have active advanced analytics access

## 5. Managing Supervisor Processes

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

## 6. What Each Process Does

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

### MQTT Subscriber Restore (`mqtt-listeners-restore.service`)
- Runs once during boot via systemd
- Restores saved per-user MQTT listener processes
- Uses `php artisan mqtt:listeners:restore`
- Replaces the old shared subscriber service model

## 7. Troubleshooting

### Check if Supervisor is Running
```bash
sudo systemctl status supervisor
```

### View All Logs
```bash
tail -f /var/www/icmqtt/storage/logs/worker.log
tail -f /var/www/icmqtt/storage/logs/scheduler.log
tail -f /var/www/icmqtt/storage/logs/mqtt-listeners-restore.log
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
   php artisan mqtt:listeners:restore --dry-run
   ```

### Check systemd restore unit
```bash
sudo systemctl status mqtt-listeners-restore.service
journalctl -u mqtt-listeners-restore.service -n 100 --no-pager
```

### Remove Configuration
```bash
sudo rm /etc/supervisor/conf.d/icmqtt.conf
sudo supervisorctl reread
sudo supervisorctl update
```

### Remove restore unit
```bash
sudo systemctl disable mqtt-listeners-restore.service
sudo rm /etc/systemd/system/mqtt-listeners-restore.service
sudo systemctl daemon-reload
```

## 8. After Code Updates

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

# Re-run subscriber restore if needed
sudo systemctl restart mqtt-listeners-restore.service
```

## 9. Monitoring

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

### Check restored listener sessions
```bash
php artisan mqtt:listeners:restore --dry-run
```

## 10. Performance Tuning

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
- `/var/www/icmqtt/storage/logs/mqtt-listeners-restore.log` - Subscriber restore logs
- `/var/log/supervisor/supervisord.log` - Supervisor logs
