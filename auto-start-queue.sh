#!/bin/bash

# Auto-start Laravel Queue Worker for background video uploads
# This script ensures queue worker is always running

QUEUE_PID_FILE="/tmp/laravel-queue.pid"
QUEUE_LOG_FILE="storage/logs/queue.log"

# Function to start queue worker
start_queue() {
    echo "Starting Laravel Queue Worker..."
    nohup php artisan queue:work --queue=uploads,default --timeout=3600 --memory=512 --tries=3 --delay=5 > $QUEUE_LOG_FILE 2>&1 &
    echo $! > $QUEUE_PID_FILE
    echo "Queue worker started with PID: $(cat $QUEUE_PID_FILE)"
}

# Function to check if queue is running
is_queue_running() {
    if [ -f $QUEUE_PID_FILE ]; then
        PID=$(cat $QUEUE_PID_FILE)
        if ps -p $PID > /dev/null; then
            return 0  # Running
        else
            rm -f $QUEUE_PID_FILE
            return 1  # Not running
        fi
    else
        return 1  # Not running
    fi
}

# Function to stop queue worker
stop_queue() {
    if [ -f $QUEUE_PID_FILE ]; then
        PID=$(cat $QUEUE_PID_FILE)
        echo "Stopping queue worker (PID: $PID)..."
        kill $PID 2>/dev/null
        rm -f $QUEUE_PID_FILE
        echo "Queue worker stopped."
    else
        echo "Queue worker is not running."
    fi
}

# Main logic
case "$1" in
    start)
        if is_queue_running; then
            echo "Queue worker is already running."
        else
            start_queue
        fi
        ;;
    stop)
        stop_queue
        ;;
    restart)
        stop_queue
        sleep 2
        start_queue
        ;;
    status)
        if is_queue_running; then
            PID=$(cat $QUEUE_PID_FILE)
            echo "Queue worker is running (PID: $PID)"
        else
            echo "Queue worker is not running."
        fi
        ;;
    auto)
        # Auto-start mode - checks every 30 seconds
        echo "Starting auto-monitor mode..."
        while true; do
            if ! is_queue_running; then
                echo "Queue worker not running, starting..."
                start_queue
            fi
            sleep 30
        done
        ;;
    *)
        echo "Usage: $0 {start|stop|restart|status|auto}"
        echo ""
        echo "Commands:"
        echo "  start   - Start queue worker"
        echo "  stop    - Stop queue worker"
        echo "  restart - Restart queue worker"
        echo "  status  - Check queue worker status"
        echo "  auto    - Auto-monitor and restart if needed"
        exit 1
        ;;
esac

exit 0
