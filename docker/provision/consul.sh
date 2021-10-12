#!/usr/bin/env bash

# Use this bash script with the following arguments
# the "--once", to start consul template
# the "--check", to check environment variables and print which do not exist

set -e

# base template file
TEMPLATE_INPUT_FILE=/app/docker/config/env.ctmpl
# prepared template files
TEMPLATE_PREPARED_FILE=/env.ctmpl
# files to replace placeholders with values from consul
TEMPLATE_OUTPUT_FILE=/app/.env
# script for run after successfully fill template
AFTER_CONSUL_FILE=/app/docker/provision/after-consul.sh
# environment variable, that stores consul address
ENV_REQUIRED=(SERVICE_NAME CONSUL_HTTP_ADDR SERVICE_KV_PATH)
# the duration in seconds to wait before consul-template daemon is terminated
TIMEOUT_DURATION=5
# stores missing consul keys (each key on a new line).
MISSING_KEYS=""
# stores missing consul keys amount.
MISSING_KEYS_AMOUNT=0
# file to store consul output
MISSING_KEYS_OUTPUT_FILE=/tmp/consul-missing-keys.txt
# temporary files to replace placeholders with values from consul
MISSING_KEYS_TEMPLATE_FILE=/tmp/consul-missing-key-template

# colors for messages: https://en.wikipedia.org/wiki/ANSI_escape_code
COLOR_DEFAULT=$'\033[0m'
COLOR_GREEN=$'\033[32m'
COLOR_RED=$'\033[31m'
COLOR_WHITE=$'\033[37m'

case $1 in
        --once)
                ONCE="-once"
                ;;
        --check)
                CHECK="--check"
                ;;
        *) ;;
esac

function execute()
{
    checkEnvVariables
    prepareTemplateFile

    if [[ $CHECK = '--check' ]]; then
        checkMissingKeys
    else
        listenTemplateFile
    fi
}

function checkEnvVariables()
{
    ENV_MISSING=()
    for i in "${ENV_REQUIRED[@]}"; do
        test -n "${!i:+y}" || ENV_MISSING+=("$i")
    done

    if [ ${#ENV_MISSING[@]} -ne 0 ]; then
        printf "${COLOR_RED}Variables aren't set: " >&2
        printf ' %q\n' "${ENV_MISSING[@]}" >&2
        printf "\n${COLOR_DEFAULT}" >&2
        exit 1
    fi
}

# run consul-template for specified duration and then terminate
# all output will be written to $MISSING_KEYS_OUTPUT_FILE
function generateMissingKeysFile() {
    set +e
    rm -rf ${MISSING_KEYS_TEMPLATE_FILE}
    /usr/local/bin/consul-template \
        -consul-addr=${CONSUL_HTTP_ADDR} \
        -kill-signal=SIGTERM \
        -log-level="trace" \
        -template="${TEMPLATE_PREPARED_FILE}:${MISSING_KEYS_TEMPLATE_FILE}" &> $MISSING_KEYS_OUTPUT_FILE
    set -e
}

function checkMissingKeys() {
    printf "${COLOR_WHITE}Checking consul keys. It will take up to ${TIMEOUT_DURATION}s.${COLOR_DEFAULT}\n"
    generateMissingKeysFile
    readMissingKeysFile
    if [[ ! -f ${MISSING_KEYS_TEMPLATE_FILE} ]] && [[ "$MISSING_KEYS_AMOUNT" -gt 0 ]]; then
        printf "${COLOR_RED}There " >&2
        test $MISSING_KEYS_AMOUNT -gt 1 \
            && printf "are $MISSING_KEYS_AMOUNT keys" >&2 \
            || printf "is $MISSING_KEYS_AMOUNT key" >&2
        printf " missing in consul:\n$MISSING_KEYS\n${COLOR_DEFAULT}Please, add " >&2
        test $MISSING_KEYS_AMOUNT -gt 1 \
            && printf "these keys" >&2 \
            || printf "this key" >&2
        printf " to consul\n" >&2
        exit 1
    else
        printf "${COLOR_GREEN}All keys are present in consul\n${COLOR_DEFAULT}"
    fi
}

function listenTemplateFile() {
    printf "${COLOR_WHITE}Listening consul keys.${COLOR_DEFAULT}\n"
    consul-template $ONCE \
        -consul-addr=${CONSUL_HTTP_ADDR} \
        -template="${TEMPLATE_PREPARED_FILE}:${TEMPLATE_OUTPUT_FILE}:bash ${AFTER_CONSUL_FILE}"
}

function prepareTemplateFile() {
    printf "${COLOR_WHITE}Prepare template file. Copy ${TEMPLATE_INPUT_FILE} to ${TEMPLATE_PREPARED_FILE}.${COLOR_DEFAULT}\n"
    cp -f $TEMPLATE_INPUT_FILE $TEMPLATE_PREPARED_FILE
    printf "${COLOR_WHITE}Prepare template file. Replace env variables in ${TEMPLATE_PREPARED_FILE}.${COLOR_DEFAULT}\n"
    /usr/local/bin/go-replace --regex \
        -s '\bSERVICE_NAME\b' -r ${SERVICE_NAME} \
        -s '\bSERVICE_KV_PATH\b' -r ${SERVICE_KV_PATH} \
        $TEMPLATE_PREPARED_FILE
}

function readMissingKeysFile() {
    MISSING_KEYS=`cat $MISSING_KEYS_OUTPUT_FILE | grep nil | sed 's/.*kv\.\(get\|block\)(\(.*\)).*/\2/'`
    if [ -n "$MISSING_KEYS" ]; then
        MISSING_KEYS_AMOUNT=`echo "$MISSING_KEYS" | wc -l`
    fi
}

execute
