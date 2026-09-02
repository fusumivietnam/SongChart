#!/bin/sh
set -eu

QUEUES="${SONGCHART_QUEUE_NAMES:-critical,discovery-projections,provider-health,provider-imports,provider-normalization,notifications,default}"
SLEEP="${SONGCHART_QUEUE_SLEEP_SECONDS:-1}"
TRIES="${SONGCHART_QUEUE_TRIES:-3}"
TIMEOUT="${SONGCHART_QUEUE_TIMEOUT_SECONDS:-150}"
BACKOFF="${SONGCHART_QUEUE_BACKOFF_SECONDS:-5}"
MAX_TIME="${SONGCHART_QUEUE_MAX_TIME_SECONDS:-3600}"
MAX_JOBS="${SONGCHART_QUEUE_MAX_JOBS:-1000}"
MEMORY="${SONGCHART_QUEUE_MEMORY_MB:-256}"

exec php artisan queue:work redis \
  --queue="$QUEUES" \
  --sleep="$SLEEP" \
  --tries="$TRIES" \
  --timeout="$TIMEOUT" \
  --backoff="$BACKOFF" \
  --max-time="$MAX_TIME" \
  --max-jobs="$MAX_JOBS" \
  --memory="$MEMORY"
