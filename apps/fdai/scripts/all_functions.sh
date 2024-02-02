#!/usr/bin/env bash
#
# Contributors: ADD YOUR NAME HERE, Mike P. Sinn | License: Open Source Under GNU General Public License v3.0
#

# bashsupport disable=BP2001,BP5008,BP5002,BP3002,BP5006,SpellCheckingInspection,BP2001,SyntaxError,BP5001
# shellcheck disable=SC2046,SC2155,SC2248,SC2086,SC2244,SC2086,SC2046
# Variables
# --------------------------------------------------------------#
## @var DEBUG
## @brief Enables / disables the debug mode.
## @details The debug mode adds extra information for troubleshooting purposes.
if [ -z ${DEBUG+x} ]; then
      export DEBUG=0
fi
function debug_enabled() {
    [[ $DEBUG == "1"   ]]
}
if debug_enabled; then # Enable xtrace if the DEBUG environment variable is set
  echo "ENABLING xtrace because DEBUG is $DEBUG..." >&2 # Prevents echo from being returned with function output
  set -o xtrace                          # Trace the execution of the script (debug)
fi
set -e
set -o errexit                  # Exit on most errors (see the manual)
#  nounset causes to many unbound variable errors:
set -o nounset                  # Disallow expansion of unset variables
set -o pipefail                 # Use last non-zero exit code in a pipeline
trap "exit 1" TERM
trap "exit" INT
export TOP_PID=$$
# https://github.com/ralish/bash-script-template/blob/stable/source.sh
# shellcheck disable=SC2034
export PHP_VERSION=7.4
export PHP_VERSION_NO_DOT=74
export JENKINS_USER=jenkins
export NGINX_USER=www # changed to be consistent with aaPanel
export PHP_IDE_CONFIG="serverName=127.0.0.1"
export WWW_ROOT=/www/wwwroot
readonly orig_cwd="$PWD" # The current working directory when the script was run
readonly script_params="$*" # The original parameters provided to the script
readonly script_path="${BASH_SOURCE[1]}"

script_dir="$(dirname "$script_path")" # The directory path of the caller script
script_name="$(basename "$script_path")" # The file name of the caller script
set +x
# CURRENT SCRIPT PATHS
# bashsupport disable=BP5005
if [[ -z ${WORKSPACE:-} ]]; then
  repo_root="$(git rev-parse --show-toplevel)"
  echo "repo_root is $repo_root" >&2 # Prevents echo from being returned with function output
  test -n "$repo_root" && cd "$repo_root" && export QM_API=$PWD;
else
  export QM_API=${WORKSPACE};
 fi
if [[ -z ${QM_API:-} ]]; then echo "could not set QM_API"; exit 1; fi
readonly SSH_KEYS="$QM_API"/configs/ssh-keys
export LINKS_FOLDER="$QM_API"/storage/links
export LOGS_FOLDER="$QM_API"/storage/logs
export IONIC_PATH=${QM_API}/ionic
export WSL_USER_NAME=vagrant
export WSL_USER_GROUP=vagrant
export HOMESTEAD_REPO=$QM_API/repos/laravel/homestead
export REPO_CONFIGS_PATH="${QM_API}/configs"
export HOME_DEV_CONFIGS="$REPO_CONFIGS_PATH/home-dev"
export DROPBOX_FOLDER="$HOME/Dropbox"
script_relative_path=${script_path##*$QM_API/}
readonly script_dir script_name
### GLOBAL PATHS
export JENKINS_HOME=/mnt/c/Jenkins/.jenkins
export REPOS_PATH=/www/wwwroot
export JENKINS_BACKUP_REPO_NAME=jenkins-backup
export JENKINS_BACKUP_REPO_PATH=${REPOS_PATH}/${JENKINS_BACKUP_REPO_NAME}
export JENKINS_EXCLUDED_FOLDERS=(
  .cache/
  .dropbox/
  .git/
  .node-gyp/
  .npm-global/
  .npm/
  .nvm/
  _gsdata_/
  backup/
  backups/
  cache/
  Dropbox/
  fingerprints/
  github-polling.log
  jenkins-backup/
  jobs/*/builds/
  lastStable
  lastSuccessful
  logs/
  luceneIndex/
  nextBuildNumber
  org.jenkinsci.plugins.github.GitHubPlugin.cache/
  plugins/
  qm-api-shared/
  tools/
  workspace/
)
export SCRIPTS_FOLDER=$QM_API/scripts
export SCRIPTS_LIB_FOLDER=$SCRIPTS_FOLDER/lib
export QM_API_SHARED=$HOME/qm-api-shared
export HOMESTEAD=$QM_API/repos/laravel/homestead
export HOMESTEAD_SCRIPTS=$HOMESTEAD/scripts
export HOMESTEAD_FEATURES=$HOMESTEAD_SCRIPTS/features
export DOT_ENV="$QM_API/.env"
export MY_CNF=.my.cnf
# Important to always set as we use it in the exit handler
# shellcheck disable=SC2155
readonly ta_none="$(tput sgr0 2>/dev/null || true)" # The ANSI control code to reset all text attributes
function _script_init() {
  set_git_envs
  if variable_is_true "TRAP"; then
      # I can't get any of this to work right and output the error location
      trap script_trap_exit EXIT # DON'T MESS WITH THESE! IT'S THE BEST YOU CAN DO
      trap catch_err $? $LINENO EXIT # DON'T MESS WITH THESE! IT'S THE BEST YOU CAN DO
  fi
  cron_init
  colour_init
}
function contains_repo_root(){
  local path=$(validate_param "${1:-}" "path" 1)
  if starts_with $path $QM_API;
    then
      return 0;
    else
      return 1;
  fi
}
function abs_path() {
  local path=$(validate_param "${1:-}" "path" 1)
  local abs_path=$QM_API/$path
  if contains_repo_root $path; then
      abs_path=$path
  fi
  if path_is_absolute $path; then
      abs_path=$path
  fi
  echo "$abs_path"
}
function copy() {
  local source=$(validate_param "${1:-}" "source" 1)
  local dest=$(validate_param "${2:-}" "dest" 2)
  local abs_source=$(abs_path $source)
  local abs_dest=$(abs_path "$dest")
  log_info "Copying $abs_source to $abs_dest"
  validate_file_path $abs_source
  cp "$abs_source" "$abs_dest"
}
function validate_file_path() {
    assert_file_exists $1
}
# prints path to git directory
function git_dirname() {
    local dirname
    dirname="$(git rev-parse --git-dir 2>/dev/null)"
    echo "$dirname"
}
## @var LOGDATEFORMAT
## @brief Sets the log data format (syslog style).
declare -x LOGDATEFORMAT="%FT%T%z"
## @var LOG_FILE
## @brief Sets the log file to use when the logs are enabled.
declare -x LOG_FILE="$0.log"
## @var LOG_ENABLED
## @brief Enables / disables logging in a file.
## @details Value: yes or no (y / n).
declare -x LOG_ENABLED="no"
## @var SYSLOG_ENABLED
## @brief Enables / disables logging to syslog.
## @details Value: yes or no (y / n).
declare -x SYSLOG_ENABLED="no"
## @var SYSLOG_TAG
## @brief Tag to use with syslog.
## @details Value: yes or no (y / n).
declare -x SYSLOG_TAG="$0"
## @var __START_WATCH
## @brief Internal use.
## @private
declare -x __START_WATCH=""
## @var __STACK
## @brief Internal use.
## @private
declare -x __STACK
## @var __TMP_STACK
## @brief Internal use.
## @private
declare -x __TMP_STACK
## @var RED
## @brief Internal color.
declare -rx RED="tput setaf 1"
# Configuration
# --------------------------------------------------------------#
# Bug fix for Bash, parsing exclamation mark.
set +o histexpand
# Groups of functions
# --------------------------------------------------------------#
## @defgroup array Array
## @defgroup command Command
## @defgroup file_and_dir File and Directory
## @defgroup log Log
## @defgroup message Message
## @defgroup misc Miscellaneous
## @defgroup network Network
## @defgroup stack Stack
## @defgroup string String
## @defgroup time Time
## @defgroup variable Variable
# Functions
# --------------------------------------------------------------#
# Group: Variable
# ----------------------------------------------------#
## @fn defined()
## @ingroup variable
## @brief Tests if a variable is defined.
## @param variable Variable to test.
## @retval 0 if the variable is defined.
## @retval 1 in others cases.
function variable_defined() {
    [[ ${!1-X} == "${!1-Y}"   ]]
}
## @fn has_value()
## @ingroup variable
## @brief Tests if a variable has a value.
## @param variable Variable to operate on.
## @retval 0 if the variable is defined and if value's length > 0.
## @retval 1 in others cases.
function variable_has_value() {
    [[ ${!1-X} == "${!1-Y}"   ]] && "$1" && [[ -n ${!1} ]]
}
function variable_is_set() {
    [[ ${!1-X} == "${!1-Y}"   ]] && "$1" && [[ -n ${!1} ]]
}
function variable_is_empty() {
    if variable_has_value $1; then
       return 1
  else
       return 0
  fi
}
## @fn option_enabled()
## @ingroup variable
## @brief Checks if a variable is set to "y" or "yes".
## @details Useful for detecting if a boolean configuration
## option is set or not.
## @param variable Variable to test.
## @retval 0 if the variable is set to "y" or "yes".
## @retval 1 in others cases.
function variable_is_true() {
    VAR_NAME="$1"
    if variable_defined $VAR_NAME; then
        VAR_VALUE=$(get_variable_value_by_name $VAR_NAME)
        if [[ $VAR_VALUE == "y"   ]] || [[ $VAR_VALUE == "yes"   ]] || [[ $VAR_VALUE == "1"   ]] || [[ $VAR_VALUE == "true"   ]]; then
            return 0 # Ridiculous, but true for bash: 0 = true
    fi
  fi
   return 1 # Ridiculous, but true for bash: 1 = false
}
# shellcheck disable=SC2091
function get_variable_value_by_name() {
    $(eval echo \$"$1")
}
# Group: File and Directory
# ----------------------------------------------------#
## @fn directory_exists()
## @ingroup file_and_dir
## @brief Tests if a directory exists.
## @param directory Directory to operate on.
## @retval 0 if the directory exists.
## @retval 1 in others cases.
function directory_exists() {
  if [[ -d $1 ]]; then
      return 0
  fi
  return 1
}
function folder_exists() {
  if [[ -d $1 ]]; then
    return 0
  fi
  return 1
}
# Group: String
# ----------------------------------------------------#
## @fn to_lower()
## @ingroup string
## @brief Converts uppercase characters in a string to lowercase.
## @param string String to operate on.
## @return Lowercase string.
function to_lower() {
    echo "$1" | tr '[:upper:]' '[:lower:]'
}
## @fn to_upper()
## @ingroup string
## @brief Converts lowercase characters in a string to uppercase.
## @param string String to operate on.
## @return Uppercase string.
function to_upper() {
    echo "$1" | tr '[:lower:]' '[:upper:]'
}
## @fn trim()
## @ingroup string
## @brief Removes whitespace from both ends of a string.
## @see <a href="https://unix.stackexchange.com/a/102021">Linux Stack Exchange</a>
## @param string String to operate on.
## @return The string stripped of whitespace from both ends.
function trim() {
    echo "${1}" | sed 's/^[[:space:]]*//;s/[[:space:]]*$//'
}
# Group: Log
# ----------------------------------------------------#
## @fn log_info()
## @ingroup log
## @brief Logs a message with its status.
## @details The log message is formatted with its status preceding its content.
## @param message Message to be logged.
## @param status Message status.
function log_message() {
    output_disable
    local MESSAGE=$(validate_param "${1:-}" "message" 1)
    echo "$MESSAGE" >&2 # Prevents echo from being returned with function output
}
## @fn log_emergency()
## @ingroup log
## @brief Logs a message with the 'emergency' status.
## @param message Message to be logged.
function log_emergency() {
    output_disable
    local MESSAGE=$(validate_param "${1:-}" "message" 1)
    STATUS="EMERGENCY"
    log_message "$STATUS: $MESSAGE"
}
## @fn log_alert()
## @ingroup log
## @brief Logs a message with the 'alert' status.
## @param message Message to be logged.
function log_alert() {
    output_disable
    local MESSAGE=$(validate_param "${1:-}" "message" 1)
    STATUS="ALERT"
    log_message "$STATUS: $MESSAGE"
}
## @fn log_error()
## @ingroup log
## @brief Logs a message with the 'error' status.
## @param message Message to be logged.
function log_error_and_stack_trace() {
    output_disable
    local MESSAGE=$(validate_param "${1:-}" "message" 1)
    STATUS="ERROR"
    log_in_box "$STATUS: $MESSAGE"
    stacktrace
    log_in_box "$STATUS: $MESSAGE"
}
## @fn log_warning()
## @ingroup log
## @brief Logs a message with the 'warning' status.
## @param message Message to be logged.
function log_warning() {
    output_disable
    local MESSAGE=$(validate_param "${1:-}" "message" 1)
    STATUS="WARNING"
    log_message "$STATUS: $MESSAGE"
}
## @fn log_info()
## @ingroup log
## @brief Logs a message with the 'info' status.
## @param message Message to be logged.
function log_info() {
    output_disable
    local MESSAGE=$(validate_param "${1:-}" "message" 1)
    STATUS="INFO"
    log_message "$STATUS: $MESSAGE"
}
## @fn log_debug()
## @ingroup log
## @brief Logs a message with the 'debug' status.
## @param message Message to be logged.
function log_debug() {
    if debug_enabled; then
      output_disable
      local MESSAGE=$(validate_param "${1:-}" "message" 1)
      STATUS="DEBUG"
      log_message "$STATUS: $MESSAGE"
  fi
}
## @fn log_ok()
## @ingroup log
## @brief Logs a message with the 'ok' status.
## @param message Message to be logged.
function log_ok() {
    output_disable
    local MESSAGE=$(validate_param "${1:-}" "message" 1)
    STATUS="OK"
    log_message "$STATUS: $MESSAGE"
}
## @fn log_not_ok()
## @ingroup log
## @brief Logs a message with the 'not ok' status.
## @param message Message to be logged.
function log_not_ok() {
    output_disable
    local MESSAGE=$(validate_param "${1:-}" "message" 1)
    STATUS="NOT_OK"
    log_message "$STATUS: $MESSAGE"
}
## @fn log_failed()
## @ingroup log
## @brief Logs a message with the 'failed' status.
## @param message Message to be logged.
function log_failed() {
    output_disable
    local MESSAGE=$(validate_param "${1:-}" "message" 1)
    STATUS="FAILED"
    log_message "$STATUS: $MESSAGE"
}
## @fn log_success()
## @ingroup log
## @brief Logs a message with the 'success' status.
## @param message Message to be logged.
function log_success() {
    output_disable
    local MESSAGE=$(validate_param "${1:-}" "message" 1)
    STATUS="SUCCESS"
    log_message "$STATUS: $MESSAGE"
}
## @fn log_passed()
## @ingroup log
## @brief Logs a message with the 'passed' status.
## @param message Message to be logged.
function log_passed() {
    output_disable
    local MESSAGE=$(validate_param "${1:-}" "message" 1)
    STATUS="PASSED"
    log_message "$STATUS: $MESSAGE"
}
# Group: Message
# ----------------------------------------------------#
## @fn log_emergency()
## @ingroup message
## @brief Displays a message with the 'emergency' status.
## @param message Message to display.
function log_emergency() {
    output_disable
    local MESSAGE=$(validate_param "${1:-}" "message" 1)
    STATUS="EMERGENCY"
    log_message "$STATUS: $MESSAGE"
}
## @fn log_alert()
## @ingroup message
## @brief Displays a message with the 'alert' status.
## @param message Message to display.
function log_alert() {
    output_disable
    local MESSAGE=$(validate_param "${1:-}" "message" 1)
    STATUS="ALERT"
    log_message "$STATUS: $MESSAGE"
}
## @fn log_critical()
## @ingroup message
## @brief Displays a message with the 'critical' status.
## @param message Message to display.
function log_critical() {
    local MESSAGE=$(validate_param "${1:-}" "message" 1)
    STATUS="CRITICAL"
    log_message "$STATUS: $MESSAGE"
}
## @fn log_notice()
## @ingroup message
## @brief Displays a message with the 'notice' status.
## @param message Message to display.
function log_notice() {
    output_disable
  local MESSAGE=$(validate_param "${1:-}" "message" 1)
  STATUS="NOTICE"
  log_in_box "$STATUS: $MESSAGE"
}
function log_in_box() {
  log_divider
  log_message "$1"
  log_divider
}
## @fn log_ok()
## @ingroup message
## @brief Displays a message with the 'ok' status.
## @param message Message to display.
function log_ok() {
    output_disable
    local MESSAGE=$(validate_param "${1:-}" "message" 1)
    STATUS="OK"
    log_message "$STATUS: $MESSAGE"
}
## @fn log_not_ok()
## @ingroup message
## @brief Displays a message with the 'not ok' status.
## @param message Message to display.
function log_not_ok() {
    output_disable
    local MESSAGE=$(validate_param "${1:-}" "message" 1)
    STATUS="NOT_OK"
    log_message "$STATUS: $MESSAGE"
}
## @fn log_failed()
## @ingroup message
## @brief Displays a message with the 'failed' status.
## @param message Message to display.
function log_failed() {
    output_disable
    local MESSAGE=$(validate_param "${1:-}" "message" 1)
    STATUS="FAILED"
    log_message "$STATUS: $MESSAGE"
}
## @fn log_success()
## @ingroup message
## @brief Displays a message with the 'success' status.
## @param message Message to display.
function log_success() {
    output_disable
    local MESSAGE=$(validate_param "${1:-}" "message" 1)
    STATUS="SUCCESS"
    log_message "$STATUS: $MESSAGE"
}
## @fn log_passed()
## @ingroup message
## @brief Displays a message with the 'passed' status.
## @param message Message to display.
function log_passed() {
    output_disable
    local MESSAGE=$(validate_param "${1:-}" "message" 1)
    STATUS="PASSED"
    log_message "$STATUS: $MESSAGE"
}
## @fn __raw_status()
## @ingroup message
## @brief Internal use.
## @private
## @details This function just positions the cursor one row
## up and to the right. It then prints the message to display
## with the specified color. It is used for displaying colored
## status messages on the right side of the screen.
## @param status Message status.
## @param color Message color.
function raw_status() {
    STATUS="$1"
    COLOR="$2"
    position_cursor()  {
        ((RES_COL = $(tput cols) - 12))
        tput cuf $RES_COL
        tput cuu1
  }
    position_cursor
    echo -n "["
    $DEFAULT
    $BOLD
    $COLOR
    echo -n "$STATUS"
    $DEFAULT
    echo "]"
}
## @fn display_status()
## @ingroup message
## @brief Displays the specified message status on the right
## side of the screen.
## @param status Message status to display.
function display_status() {
    STATUS="$1"
    case $STATUS in
        EMERGENCY)
            STATUS="EMERGENCY"
            COLOR="$RED"
            ;;
        ALERT)
            STATUS="  ALERT  "
            COLOR="$RED"
            ;;
        CRITICAL)
            STATUS="CRITICAL "
            COLOR="$RED"
            ;;
        ERROR)
            STATUS="  ERROR  "
            COLOR="$RED"
            ;;
        WARNING)
            STATUS=" WARNING "
            COLOR="$YELLOW"
            ;;
        NOTICE)
            STATUS=" NOTICE  "
            COLOR="$BLUE"
            ;;
        INFO)
            STATUS="  INFO   "
            COLOR="$CYAN"
            ;;
        DEBUG)
            STATUS="  DEBUG  "
            COLOR="$DEFAULT"
            ;;
        OK)
            STATUS="   OK    "
            COLOR="$GREEN"
            ;;
        NOT_OK)
            STATUS=" NOT OK  "
            COLOR="$RED"
            ;;
        PASSED)
            STATUS=" PASSED  "
            COLOR="$GREEN"
            ;;
        SUCCESS)
            STATUS=" SUCCESS "
            COLOR="$GREEN"
            ;;
        FAILURE | FAILED)
            STATUS=" FAILED  "
            COLOR="$RED"
            ;;
        *)
            STATUS="UNDEFINED"
            COLOR="$YELLOW"
      ;;
  esac
    raw_status "$STATUS" "$COLOR"
}
# Group: Command
# ----------------------------------------------------#
## @fn log_command()
## @ingroup command
## @brief Executes a command and displays its status ('OK' or 'FAILED').
## @param command Command to execute.
function command() {
    COMMAND="$*"
    log_info "Executing: $COMMAND"
    RESULT=$(eval "$COMMAND" 2>&1)
    ERROR="$?"
    MSG="Command: ${COMMAND:0:29}..."
    tput cuu1
    if [ "$ERROR" == "0" ]; then
        log_ok "$MSG"
        log_debug "$RESULT"
  else
        log_failed "$MSG"
  fi
    return "$ERROR"
}
# Group: Command
# ----------------------------------------------------#
## @fn log_command()
## @ingroup command
## @brief Executes a command and displays its status ('OK' or 'FAILED').
## @param command Command to execute.
function command_with_output() {
    COMMAND="$*"
    log_info "Executing: $COMMAND"
    output_enable
    $COMMAND
    ERROR="$?"
    MSG="Command: ${COMMAND:0:29}..."
    tput cuu1
    if [ "$ERROR" == "0" ]; then
        log_ok "$MSG"
        log_debug "$RESULT"
  else
        log_failed "$MSG"
  fi
    return "$ERROR"
}
# Group: Time
# ----------------------------------------------------#
## @fn now()
## @ingroup time
## @brief Displays the current timestamp.
## @return Current timestamp.
function now() {
    date +%s
}
## @fn elapsed()
## @ingroup time
## @brief Displays the time elapsed between the 'start' and 'stop'
## parameters.
## @param start Start timestamp.
## @param stop Stop timestamp.
## @return Time elapsed between the 'start' and 'stop' parameters.
function elapsed() {
    START="$1"
    STOP="$2"
    ELAPSED=$((STOP - START))
    echo $ELAPSED
}
## @fn die_if_false()
## @ingroup misc
## @brief Displays an error message and exits if the previous
## command has failed (if its error code is not '0').
## @param errcode Error code.
## @param errmsg Error message.
function die_if_false() {
    local -r err_code=$1
    if [[ $err_code != "0"   ]]; then
        local -r err_msg=$2
        die "$err_code" "$err_msg"
  fi
}
## @fn die_if_true()
## @ingroup misc
## @brief Displays an error message and exits if the previous
## command has succeeded (if its error code is '0').
## @param errcode Error code.
## @param errmsg Error message.
function die_if_true() {
    if [[ $err_code == "0"   ]]; then
        local -r err_msg=$2
        die "$err_code" "$err_msg"
  fi
}
# Group: Array
# ----------------------------------------------------#
## @fn __array_append()
## @ingroup array
## @brief Internal use.
## @private
## @param array Array name.
## @param item Item to append.
# shellcheck disable=SC2016
function array_append() {
    echo -n 'eval '
    echo -n "$1" # array name
    echo -n '=( "${'
    echo -n "$1"
    echo -n '[@]}" "'
    echo -n "$2" # item to append
    echo -n '" )'
}
## @fn __array_append_first()
## @ingroup array
## @brief Internal use.
## @private
## @param array Array name.
## @param item Item to append.
function array_append_first() {
    echo -n 'eval '
    echo -n "$1" # array name
    echo -n '=( '
    echo -n "$2" # item to append
    echo -n ' )'
}
## @fn __array_len()
## @ingroup array
## @brief Internal use.
## @private
## @param variable Variable name.
## @param array Array name.
# shellcheck disable=SC2016
array_len() {
    echo -n 'eval local '
    echo -n "$1" # variable name
    echo -n '=${#'
    echo -n "$2" # array name
    echo -n '[@]}'
}
## @fn array_append()
## @ingroup array
## @brief Appends one or more items to an array.
## @details If the array does not exist, this function will create it.
## @param array Array to operate on.
# shellcheck disable=SC2091
array_append() {
    local array=$1
                    shift 1
    local len
    $(array_len len "$array")
    if ((len == 0)); then
        $(array_append_first"$array" "$1")
        shift 1
  fi
    local i
    for i in "$@"; do
        $(array_append"$array" "$i")
  done
}
## @fn array_size()
## @ingroup array
## @brief Returns the size of an array.
## @param array Array to operate on.
## @return Size of the array given as parameter.
# shellcheck disable=SC2091
array_size() {
    local size
    $(array_lensize "$1")
    echo "$size"
}
## @fn array_print()
## @ingroup array
## @brief Prints the content of an array.
## @param array Array to operate on.
## @return Content of the array given as parameter.
array_print() {
    eval "printf '%s\n' \"\${$1[@]}\""
}
# Group: String
# ----------------------------------------------------#
####################
### String Utilities
####################
string_title() {
    local string="${1}"
    if [[ -z ${string}   ]] && [ ! -t 0 ]; then
        string=$(cat <&0)
  fi
    echo "${string}" | sed -E 's/\b(\w)/\u\1/g'
}
## @fn str_replace()
## @ingroup string
## @brief Replaces some text in a string.
string_replace() {
    local original=$(validate_param "${1:-}" "original" 1)
    local search=$(validate_param "${1:-}" "search" 1)
    local replace=${2:-""}
    local new="${original/$search/$replace}"
    echo $new
}
# Group: Stack
# ----------------------------------------------------#
## @fn __stack_push_tmp()
## @ingroup stack
## @brief Internal use.
## @private
## @param item Item to add on the temporary stack.
stack_push_tmp() {
    local TMP="$1"
    if variable_has_value __TMP_STACK; then
        __TMP_STACK="${__TMP_STACK}"$'\n'"${TMP}"
  else
        __TMP_STACK="$TMP"
  fi
}
## @fn stack_push()
## @ingroup stack
## @brief Adds an item on the stack.
## @param item Item to add on the stack.
stack_push() {
    line="$1"
    if variable_has_value __STACK; then
        __STACK="${line}"$'\n'"${__STACK}"
  else
        __STACK="$line"
  fi
}
### END IMPORTED FROM scripts/lib/bsfl/lib/bsfl.sh ###### START IMPORTED FROM scripts/lib/env_functions.sh ###
# Group: Env Functions
# ----------------------------------------------------##!/usr/bin/env bash
#######################################
# description
# Globals:
#   APP_ENV
#   ENV_PATH$QM_API
# Arguments:
#   1
#######################################
# shellcheck disable=SC2120
function source_dotenv() {
  log_start_of_function
  env_path=${1:-".env"}
  env_path=$QM_API/${env_path}
  log_warning "LOADING ENVIRONMENTAL VARIABLES FROM path: ${env_path}"
  log_warning "Sourcing .env before running tests will fuck up the Laravel env loader!"
  validate_file_exists "${env_path}"
  export $(grep -v '#.*' .env | xargs)
  validate_variable_set APP_ENV
  log_end_of_function
}
#######################################
  # Usage = add_to_dot_env NAME VALUE ENV_FILE_PATH
# Arguments:
#   1 NAME
#   2 VALUE
#   3 ENV_FILE_PATH
#######################################
function add_to_dotenv() {
  log_debug "add_to_dotenv: $1"
  output_disable
  local variable_name=$(validate_param "${1:-}" "variable_name" 1)
  local env_path=$(validate_param "${2:-}" "env path" 2)
  env_path=$(abs_path $env_path)
  local variable_value=""
  if [ -n "${!variable_name-}" ]; then
      variable_value="${!variable_name}" # outputs 'this is the real value'
  elif [ "${variable_name+defined}" = defined ]; then
      log_warning "${variable_name} empty but defined so setting to empty string "
  else
      log_warning "${variable_name} unset so setting to empty string "
  fi
  remove_line_containing "$variable_name=" $env_path
  add_empty_line_to_file $env_path
  add_line_to_file "${variable_name}=${variable_value}" "${env_path}"
}
function get_app_env(){
  echo ${APP_ENV:-$(get_var_from_dotenv APP_ENV)}
}
# bashsupport disable=BP3001
# Gets ENV property from provided .env file
#
# @param var - Variable name
# @param path - Path to env file.
# @param [file] - Optional fileName param. @default .env.
# shellcheck disable=SC2015,SC2062
function get_var_from_dotenv() {
  local variable_name=$(validate_param "${1:-}" "variable name" 1)
  local folder=${2:-$QM_API}
  local file_name=${3:-.env}
  # This line is necessary to parse function named args
  # @see https://gist.github.com/mopcweb/38f5d09525f8defa5aa807d95efa8307
  while [[ $# -gt 0 ]]; do
    if [[ $variable_name == *"--"* ]]; then
      if [[ $folder != *"--"* ]]; then
          local "${variable_name/--/}"="${folder:-true}"
      else
          local "${variable_name/--/}"=true
      fi
    fi
        shift
  done
  validate_file_path "$folder/$file_name"
  local result=$(grep ^$variable_name=.* $folder/$file_name | cut -d "=" -f 2)
  [[ -z $result ]] && exit_if_error1 "get_env_var: there is no such $variable_name var in $folder/$file_name file."
  echo $result
}
#######################################
# env_copy "${RELEASE_STAGE}"-remote
# Arguments:
#   1 - release stage name
#   2 - destination env (default: $QM_API/.env)
#######################################
function env_copy() {
  log_start_of_function
  go_to_repo_root
  local release_stage=$(validate_param "${1:-}" "source env name i.e. production" 1)
  php scripts/php/env.php $release_stage
  assert_file_contains .env "DO_SPACES_KEY"
  log_end_of_function
}
function assert_file_contains() {
	local file=$(validate_param "${1:-}" "file" 1)
	local expected_string=$(validate_param "${2:-}" "expected_string" 2)
  if grep -q $expected_string "$file"; ##note the space after the string you are searching for
  then
    log_info "$file contains $expected_string"
  else
    die "$file does not contain $expected_string"
  fi
}
function contains() {
  local haystack=$(validate_param "${1:-}" "haystack" 1)
  local needle=$(validate_param "${2:-}" "needle" 2)
   if [[ $haystack == *"$needle"* ]]; then
        return 0  # Ridiculous, but true for bash: 0 = true
  else
        return 1 # Ridiculous, but false for bash: 1 = false
  fi
}
function path_is_absolute() {
  local path=$(validate_param "${1:-}" "path" 1)
    if starts_with $path "/";
    then
      return 0;  # Ridiculous, but true for bash: 0 = true
    else
      return 1;  # Ridiculous, but false for bash: 1 = false
  fi
}
function starts_with() {
  local haystack=$(validate_param "${1:-}" "haystack" 1)
  local needle=$(validate_param "${2:-}" "needle" 2)
   if [[ $haystack == "$needle"*   ]]; then
        return 0  # Ridiculous, but true for bash: 0 = true
  else
        return 1  # Ridiculous, but true for bash: 0 = true
  fi
}
### END IMPORTED FROM scripts/lib/env_functions.sh ###### START IMPORTED FROM scripts/lib/filesystem_functions.sh ###
# Group: Filesystem Functions
# ----------------------------------------------------##!/usr/bin/env bash
#######################################
# description
# Globals:$QM_API
# Arguments:
#  None
#######################################
function clean_qm_api_tmp() {
  sudo mkdir /tmp || true
  echo "Cleaning tmp because I used to clone all the repos there and it used to much space so I switched to the shared/tmp.
	You can probably remove the below line after all the repos are removed from the test servers."
  rm -rf "$QM_API"/tmp/*
}
#######################################
# description
# Arguments: rsync_remote $remote_host $remote_user $source_dir $destination_dir
#   1 remote_host
#   2 remote_user
#   3 source_dir
#   4 destination_dir
#######################################
function rsync_remote() {
  local remote_host=$(validate_param "${1}" "HOSTNAME" 1)
  local remote_user=$(validate_param "${2}" "remote_user" 2)
  local source_dir=$(validate_param "${3}" "local source directory" 3)
  local destination_dir=$(validate_param "${4}" "destination_dir" 4)
  exclude="--exclude .git/ --exclude node_modules/ --exclude qm-staging/ --exclude qm-production/ --exclude storage/framework/cache/data/"
  rsync_options="--info=progress2 --checksum --stats --omit-dir-times --no-perms --no-owner --no-group --delete"
  # Uncomment to use --itemize-changes if you need to list changes for debugging
  #rsync_options="--checksum --itemize-changes --stats --omit-dir-times  --no-perms --no-owner --no-group --delete"
  #log_in_box "RSYNC ${remote_user}@${remote_host} to ${destination_dir}"
  output_enable
  rsync -am ${exclude} ${rsync_options} ${source_dir}/ ${remote_user}@${remote_host}:${destination_dir}/
  output_disable
}
#######################################
# usage assert_file_exists ${J_UNIT_FILE}
# Arguments:
#   1 - File name
#######################################
function assert_file_exists() {
  local file_path=$(validate_param "${1:-}" "file_path" 1)
  if [[ ! -f $file_path ]]; then
    die "${file_path} does not exist! ${2:-}"
  fi
}
#######################################
# usage assert_file_exists ${J_UNIT_FILE}
# Arguments:
#   1 - File name
#######################################
function assert_file_does_not_exist() {
  local file=$(validate_param "${1:-}" "file path" 1)
  if [ -f $file ]; then
    die "File $file should not exist! ${2:-}"
  else
    log_debug "File $file does not exist. ${2:-}"
  fi
  echo $file
}
function wsl_configs_copy_and_restart() {
  log_start_of_function
  set_app_env_local
	ssh_restart
	etc_copy_and_restart
  log_info "Fixing WSL home error..."
  sudo usermod -d /var/lib/mysql/ mysql
	home_dev_copy $WSL_USER_NAME /home/$WSL_USER_NAME
	log_end_of_function
}
function set_app_env_local(){
  set_app_env "local"
}
function set_app_env() {
	export APP_ENV=$(validate_param "${1:-}" "APP_ENV" 1)
	log_info "Setting APP_ENV to $APP_ENV";
}

function etc_copy_and_restart() {
  log_start_of_function
  if [ -z "${APP_ENV:-}" ]; then
    log_info "Getting APP_ENV from .env because you didn't set it before calling etc_files_copy"
    source_dotenv
  fi
  log_info "APP_ENV is $APP_ENV"
  log_info "Removing old nginx configs..."
  output_enable
  sudo rm -rf /etc/nginx/sites-enabled/* || true
  sudo rm -rf /etc/nginx/quantimodo.*.nginx.conf || true
  sudo rm -rf /etc/nginx/*.qm.nginx.conf || true
  sudo cp -R "${REPO_CONFIGS_PATH}"/etc-global/* /etc/
  output_disable
  if [[ ${APP_ENV} == *"local"* || ${APP_ENV} == *"dev"* || ${APP_ENV} == *"testing"* ]]; then
    echo "Copying etc-local because APP_ENV is ${APP_ENV}"
    sudo cp -R "${REPO_CONFIGS_PATH}"/etc-dev/* /etc/
  fi
  restart_services
  if [ -f "/etc/.git" ]; then
    etckeeper_push "After etc_files_copy"
  else
    log_message "etckeeper not installed!"
  fi
  log_end_of_function
}
function xdebug_311_install_aapanel() {
  log_start_of_function
#sudo apt-get install php$PHP_VERSION-dev -y
log_info "https://guides.wp-bullet.com/using-free-tideways-xhprof-xhgui-php-7-x-for-wordpress-code-profiling/"
log_info "https://forum.aapanel.com/d/667-install-additional-php-extensions/5"
log_info "https://xdebug.org/docs/install"
mkdir "$QM_API"/tmp || true
cd "$QM_API"/tmp
wget https://xdebug.org/files/xdebug-3.1.1.tgz
tar -xvzf xdebug-3.1.1.tgz
cd xdebug-3.1.1
/www/server/php/$PHP_VERSION_NO_DOT/bin/phpize
./configure --with-php-config=/www/server/php/$PHP_VERSION_NO_DOT/bin/php-config
make
sudo make install
#cp modules/xdebug.so "no value"

log_info "
Copy
configs/aapanel/www/server/php/$PHP_VERSION_NO_DOT/etc/php.ini
to
/www/server/php/$PHP_VERSION_NO_DOT/etc/php.ini
"
log_end_of_function
}
function xdebug_30_install() {
  set_app_env_local
	install_unattended php${PHP_VERSION}-xdebug
  sudo phpenmod -s cli xdebug
  restart_web_services
}
#######################################
# usage: $QM_API
# Arguments:
#  None
#######################################
function qm_api() {
    validate_variable_set QM_API
    echo $QM_API
}
#######################################
# remove_line_containing $pattern $file
# Arguments:
#   1 pattern
#   2 file
#######################################
function remove_line_containing() {
  local pattern=$(validate_param "${1:-}" "pattern" 1)
  local file=$(validate_param "${2:-}" "file" 2)
  file=$(abs_path $file)
  log_info "remove_line_containing: $pattern from file $file"
  assert_file_exists $file
  sed -i "/$pattern/d" $file
}
#######################################
# description
# Arguments:
#   1
#######################################
function delete_if_symlink() {
  local my_link=$(validate_param "${1:-}" "the link you want to delete" 1)
  if [ -L ${my_link} ] ; then
     if [ -e ${my_link} ] ; then
        log_info "Deleting existing symlink $my_link..."
     else
        log_info "Deleting broken symlink $my_link..."
     fi
     sudo rm -v "$my_link"
  elif [ -e ${my_link} ] ; then
     log_warning "${my_link} is not a link so not deleting!"
  else
     log_debug "${my_link} does not exist so not deleting!"
  fi
}
#######################################
# description
# Arguments:
#   1
#   2
#######################################
function folder_to_link() {
  log_start_of_function
  new_line
  local folder=$(validate_param "${1:-}" "folder to link to" 1)
  local link=$(validate_param "${2:-}" "where you want the link" 2)
  if [ -d "$folder" ];
    then
      delete_if_symlink "$link" # Update link without deleting folders
      log_info "Creating symlink $link to the folder: ${folder}"
     # https://unix.stackexchange.com/questions/440905/what-is-the-meaning-of-ln-st-in-linux
      sudo ln -sT "${folder}" "$link" >/dev/null 2>&1
    else
      log_warning "Cannot create symlink because $folder does not exist"
  fi
  new_line
  log_end_of_function
}
function link_source_to_link() {
  log_start_of_function
  new_line
  local source=$(validate_param "${1:-}" "source path to link to" 1)
  local link=$(validate_param "${2:-}" "where you want the link" 2)
  delete_if_symlink "$link" # Update link without deleting folders
  log_info "Creating symlink $link to the source: ${source}"
  # https://unix.stackexchange.com/questions/440905/what-is-the-meaning-of-ln-st-in-linux
  sudo ln -sT "${source}" "$link"
  new_line
  log_end_of_function
}
function set_owners_on_file_or_folder(){
  log_start_of_function
  local original_location=$(validate_param "${1:-}" "original folder or file you want to own" 1)
  if [[ -d $original_location ]]; then
    log_message "Setting owner $WSL_USER_NAME:$NGINX_USER on FOLDER: $original_location"
    sudo chown -R $WSL_USER_NAME:$NGINX_USER "$original_location"
  elif [[ -f $original_location ]]; then
    log_message "Setting owner $WSL_USER_NAME:$NGINX_USER on FILE: $original_location"
    sudo chown $WSL_USER_NAME:$NGINX_USER "$original_location"
  else
      echo "$original_location is not valid"
      exit 1
  fi
  log_end_of_function
}
function move_and_replace_with_link(){
  log_start_of_function
  local original_location=$(validate_param "${1:-}" "original folder or file you want to move" 1)
  local new_location=$(validate_param "${2:-}" "where you want to move it" 2)
  if [[ -L "$original_location" ]]; then
    log_info "Skipping $original_location because it's already a symlink"
    return;
  fi
  log_info "Moving $original_location to $new_location..."
  set_owners_on_file_or_folder $original_location
  mkdir -p $(dirname $new_location)
  mv $original_location "$new_location"
  log_info "Creating symlink from original location $original_location to the new location: ${new_location}"
  # ln -s source_file symbolic_link
  # https://unix.stackexchange.com/questions/440905/what-is-the-meaning-of-ln-st-in-linux
  sudo ln -sT "${new_location}" "$original_location"
  log_end_of_function
}
function new_line(){
  echo "" >&2
}
#######################################
# add_line_to_file "${string}" "${file}"
#######################################
function add_line_to_file() {
  local string=$(validate_param "${1:-}" "string" 2)
  local file=$(validate_param "${2:-}" "file" 2)
  if [[ -f $file  ]]; then
    echo "${string}" >>"${file}"
  else
    die "$file does not exist! so cannot add $string"
  fi
}
#######################################
# add_empty_line_to_file
#######################################
function add_empty_line_to_file() {
  local file=$(validate_param "${1:-}" "file" 3)
  validate_file_exists $file
  echo "" >>"${file}"
}
# DESC: Combines two path variables and removes any duplicates
# ARGS: $1 (required): Path(s) to join with the second argument
#       $2 (optional): Path(s) to join with the first argument
# OUTS: $build_path: The constructed path
# NOTE: Heavily inspired by: https://unix.stackexchange.com/a/40973
function build_path() {
  if [[ $# -lt 1 ]]; then
    script_exit 'Missing required argument to build_path()!' 2
  fi
  local new_path path_entry temp_path
  temp_path="$1:"
  if [[ -n ${2-} ]]; then
    temp_path="$temp_path$2:"
  fi
  new_path=
  while [[ -n $temp_path ]]; do
    path_entry="${temp_path%%:*}"
    case "$new_path:" in
      *:"$path_entry":*) ;;
      *)
        new_path="$new_path:$path_entry"
        ;;
    esac
    temp_path="${temp_path#*:}"
  done
  # shellcheck disable=SC2034
  build_path="${new_path#:}"
}
#######################################
# description
# Arguments:
#   1 - path
#   2 - optional message
#######################################
function validate_file_exists() {
  output_disable
   if [[ -f $1   ]]; then
    log_debug "$1 exists"
  else
    die "$1 does not exist! ${2:-}"
  fi
}
### END IMPORTED FROM scripts/lib/filesystem_functions.sh ###### START IMPORTED FROM scripts/lib/git_functions.sh ###
# Group: Git Functions
# ----------------------------------------------------##!/usr/bin/env bash
#######################################
# description
# Usage: github_update_status.sh --sha=some_sha \
#   --repo=mikepsinn/service-identity \
#   --status=pending \
#   --message="Starting tests" \
#   --context=mikepsinn/e2e \
#   --url=http://something.com
# Globals:
#   BUILD_URL
#   CHECK_CONTEXT
#   CHECK_MESSAGE
#   COMMIT_SHA
#   GITHUB_TOKEN
#   REPO_SLUG
#   STATUS
#   i
# Arguments:
#  None
#######################################
function github_update_status() {
  local status=${STATUS:-"pending"}
  local repo_slug=${REPO_SLUG:-}
  build_url=${BUILD_URL:-}
  for i in "$@"; do
    case $i in
      -s=* | --sha=*)
        local commit_sha="${i#*=}"
        shift # past argument=value
        ;;
      -r=* | --repo=*)
        repo_slug="${i#*=}"
        shift # past argument=value
        ;;
      -m=* | --message=*)
        local check_message="${i#*=}"
        shift # past argument=value
        ;;
      -u=* | --url=*)
        build_url="${i#*=}"
        shift # past argument=value
        ;;
      --status=*)
        status="${i#*=}"
        shift # past argument=value
        ;;
      -c=* | --context=*)
        local check_context="${i#*=}"
        shift # past argument with no value
        ;;
      *)
        # unknown option
        ;;
    esac
  done
  echo "COMMIT_SHA  = ${commit_sha}"
  echo "REPO_SLUG   = ${repo_slug}"
  echo "STATUS    = ${status}"
  echo "CHECK_CONTEXT    = ${check_context}"
  echo "BUILD_URL    = ${build_url}"
  echo "CHECK_MESSAGE    = ${check_message}"
  echo $check_message
  log_in_box "$(git_commit_message) tests $status"
  curl -u mikepsinn:"${GITHUB_TOKEN}" \
    --header "Content-Type: application/json" \
    --data '{"state": "'$status'", "context": "'$check_context'", "description": "'"$check_message"'", "target_url": "'$build_url'"}' \
    --request POST \
    https://api.github.com/repos/"$repo_slug"/statuses/$commit_sha
}
#######################################
# description
# Globals:
# Arguments:
#  None
#######################################
function github_mark_pending() {
  parse_open_source_test_repo_commit_message
  github_update_status_for_open_source_test_repo pending
}
#######################################
# description
# Globals:
# Arguments:
#  None
#######################################
function github_mark_successful() {
  parse_open_source_test_repo_commit_message
  github_update_status_for_open_source_test_repo success
}
#######################################
# Arguments:
#  None
#######################################
function github_mark_failed() {
  parse_open_source_test_repo_commit_message
  github_update_status_for_open_source_test_repo failure
}
function github_update_status_for_open_source_test_repo() {
  local status=$(validate_param "${1}" "status" 1)
  parse_open_source_test_repo_commit_message
  github_update_status --sha="${SHA}" \
    --repo="${REPO_TO_TEST}" \
    --status="$1" \
    --message="$(git_commit_message) $status on Travis..." \
    --context="${TEST_PATH}" \
    --url="${TRAVIS_BUILD_URL}"
}
function parse_open_source_test_repo_commit_message() {
  export TEST_PATH=$(echo "${TRAVIS_COMMIT_MESSAGE}" | cut -f1 -d#)
  export TESTED_BRANCH=$(echo "${TRAVIS_COMMIT_MESSAGE}" | cut -f2 -d#)
  export TESTED_SHA=$(echo "${TRAVIS_COMMIT_MESSAGE}" | cut -f3 -d#)
  export TRAVIS_BUILD_URL=https://travis-ci.org/${TRAVIS_REPO_SLUG}/builds/${TRAVIS_BUILD_ID}
  export REPO_TO_TEST=qm-api
}
#######################################
# description
#######################################
function git_stash_and_pull_and_pop() {
  log_start_of_function
  stash=0
  git_stash
  branch=$(git branch | grep "\*" | cut -d " " -f 2-9)
  if [[ $branch == "$1" ]]; then
    git pull origin "$1"
  else
    git checkout "$1"
    git pull origin "$1"
  fi
  sleep 3
  log_end_of_function
}

# shellcheck disable=SC2196
function git_delete_merged_branches() {
  set_git_envs
  log_info "Deleting local branches that have been merged to $(get_git_branch) already"
  # master|develop are protected.  Add more there if necessary
  git branch --merged | egrep -v "(^\*|main|master|develop)" | xargs -r git branch -d
}
#######################################
# description
#######################################
# bashsupport disable=BP5006
function set_git_envs() {
  export COMMIT_MESSAGE=$(git_commit_message)
  export GIT_BRANCH=$(get_git_branch)
  export GIT_COMMIT=${CIRCLE_SHA1:-${GIT_COMMIT:-$(git rev-parse HEAD 2>/dev/null | sed "s/\(.*\)/\1/")}}
  export GITHUB_ACCESS_TOKEN=${GIT_ACCESS_TOKEN:-${GITHUB_ACCESS_TOKEN:-$(get_var_from_dotenv GITHUB_ACCESS_TOKEN)}}
}
function github_access_token() {
    export GITHUB_ACCESS_TOKEN=${GIT_ACCESS_TOKEN:-${GITHUB_ACCESS_TOKEN:-$(get_var_from_dotenv GITHUB_ACCESS_TOKEN)}}
    validate_variable_set GITHUB_ACCESS_TOKEN
    echo $GITHUB_ACCESS_TOKEN
}
#######################################
# description
# Globals:
#   stash
# Arguments:
#  None
#######################################
function git_stash() {
  # check if we have un-committed changes to stash
  git status --porcelain | grep "^." >/dev/null
  # shellcheck disable=SC2181
  stash=0
  if [[ $? -eq 0 ]]; then
    if git stash save -u "git-update on $(date)"; then
      stash=1
    fi
  fi
  # check if we have un-committed change to restore from the stash
  if [[ ${stash} -eq 1 ]]; then
    git stash pop
  fi
}
# checks if branch has something pending
function parse_git_dirty() {
  git diff --quiet --ignore-submodules HEAD 2>/dev/null
  [[ $? -eq 1 ]] && echo "*"
}
# gets the current git branch
function get_git_branch() {
  export GIT_BRANCH=${BUDDYBUILD_BRANCH:-${CIRCLE_BRANCH:-${TRAVIS_BRANCH:-${GIT_BRANCH:-$(git branch | grep "\*" | cut -d " " -f 2-9)}}}}
  echo $GIT_BRANCH
}
function git_commit_message(){
  export COMMIT_MESSAGE=${COMMIT_MESSAGE:-$(git log -1 HEAD --pretty=format:%s)}
  echo $COMMIT_MESSAGE
}

function get_commit_date() {
  git log -1 --format=%ci
}
#######################################
# description
#######################################
function output_commit_message_and_env() {
  output_disable
  log_in_box "Building $(git_commit_message) on $(get_git_branch) as ${USER}"
  #printenv
  php -v || true
}
#######################################
# description
# Globals:
#   GITHUB_ACCESS_TOKEN
#   GIT_ACCESS_TOKEN
# Arguments:
#  None
#######################################
function git_config() {
  # set_git_envs
  output_enable
  git config --global user.email ${GIT_USER_EMAIL:-"m@quantimodo.com"}
  git config --global user.name ${GIT_USER_NAME:-"Mike Sinn"}
  git config --global core.autocrlf false
  git config --global core.eol lf
  output_disable
  log_info "git config --global github.token [HIDDEN]"
  git config --global github.token "$(github_access_token)"
}
#######################################
# description
# Globals:
#   _current_branch
# Arguments:
#  None
#######################################
function push() {
    _current_branch=$(git branch | sed -n -e 's/^\* \(.*\)/\1/p')
    sudo git add -A
    sudo git commit -m "quick push"
    sudo git push $_current_branch
}
#######################################
# description
# Globals:
#   _current_branch
# Arguments:
#  None
#######################################
function quick_pull() {
    _current_branch=$(git branch | sed -n -e 's/^\* \(.*\)/\1/p')
    sudo git pull $_current_branch
}
#######################################
# description
# Globals:
#   _current_branch
# Arguments:
#  None
#######################################
function pull() {
    _current_branch=$(git branch | sed -n -e 's/^\* \(.*\)/\1/p')
    sudo git pull $_current_branch
}
function output_debug_info() {
  log_info "Script Path: $script_path"
  log_info "Script Parameters:  $script_params"
}
### END IMPORTED FROM scripts/lib/git_functions.sh ###### START IMPORTED FROM scripts/lib/init_functions.sh ###
# Group: Init Functions
# ----------------------------------------------------##!/usr/bin/env bash
# https://github.com/ralish/bash-script-template/blob/stable/source.sh
# DESC: Handler for unexpected errors
# ARGS: $1 (optional): Exit code (defaults to 1)
# OUTS: None
function catch_err() {
  echo "catch_err called!"
  if [ "$1" != "0" ]; then
    # error handling goes here
    echo "ERROR CODE $1 occurred on LINE NUMBER ${2:-}"
  fi
    exit "$1"
}
# DESC: Handler for exiting the script
# ARGS: None
# https://github.com/ralish/bash-script-template/blob/stable/source.sh
# OUTS: None
function script_trap_exit() {
  output_debug_info
  go_to "$orig_cwd"
  # Restore terminal colours
  printf '%b' "$ta_none"
}
# DESC: Exit script with the given message
# ARGS: $1 (required): Message to print on exit
#       $2 (optional): Exit code (defaults to 0)
# OUTS: None
# NOTE: The convention used in this script for exit codes is:
#       0: Normal exit
#       1: Abnormal exit due to external error
#       2: Abnormal exit due to script error
function script_exit() {
  echo "script_exit called!"
  if [[ $# -eq 1 ]]; then
    printf '%s\n' "$1"
    exit 0
  fi
  if [[ ${2-} =~ ^[0-9]+$ ]]; then
    printf '%b\n' "$1"
    # If we've been provided a non-zero exit code run the error trap
    if [[ $2 -ne 0 ]]; then
      script_trap_err "$2"
    else
      exit 0
    fi
  fi
  script_exit 'Missing required argument to script_exit()!' 2
}
# DESC: Initialise Cron mode
# ARGS: None
# https://github.com/ralish/bash-script-template/blob/stable/source.sh
# OUTS: $script_output: Path to the file stdout & stderr was redirected to
function cron_init() {
  if [[ -n ${cron-} ]]; then
    # Redirect all output to a temporary file
    script_output="$(mktemp --tmpdir "$script_name".XXXXX)"
    readonly script_output
    exec 3>&1 4>&2 1>"$script_output" 2>&1
  fi
}
# DESC: Acquire script lock
# ARGS: $1 (optional): Scope of script execution lock (system or user)
# OUTS: $script_lock: Path to the directory indicating we have the script lock
# NOTE: This lock implementation is extremely simple but should be reliable
#       across all platforms. It does *not* support locking a script with
#       symlinks or multiple hardlinks as there's no portable way of doing so.
#       If the lock was acquired it's automatically released on script exit.
function lock_init() {
  local lock_dir
  if [[ $1 == 'system' ]]; then
    lock_dir="/tmp/$script_name.lock"
  elif [[ $1 == 'user' ]]; then
    lock_dir="/tmp/$script_name.$UID.lock"
  else
    script_exit 'Missing or invalid argument to lock_init()!' 2
  fi
  if mkdir "$lock_dir" 2>/dev/null; then
    readonly script_lock="$lock_dir"
    verbose_print "Acquired script lock: $script_lock"
  else
    script_exit "Unable to acquire script lock: $lock_dir" 1
  fi
}
function explode() {
  local str=$(validate_param "${1:-}" "str" 1)
  local delimiter=$(validate_param "${2:-}" "delimiter" 2)
  local arr=$(echo $str | tr "$delimiter" "\n")
  echo $arr
}
### END IMPORTED FROM scripts/lib/init_functions.sh ###### START IMPORTED FROM scripts/lib/laravel_bash_helpers.sh ###
# Group: Laravel Bash Helpers
# ----------------------------------------------------##!/usr/bin/env bash
# shellcheck disable=SC2184
find_file() {
    die "TODO"
    local path
    local directories=$(explode_path "$PWD")
    while [ ${#directories[@]} -gt 0 ]; do
        path="$( IFS=/ && printf '%s' "${directories[*]}")/$*"
        if [ -e "$path" ]; then
            echo "$path"
            return
    fi
        unset directories[-1]
  done
    die "Could not find $1"
}
filter_by_regex() {
    : ${1:?You must provide a pattern}
    local pattern=$1
    shift
    for word in "$@"; do
        [[ $word =~ $pattern   ]] && echo "$word"
  done
}
artisan() {
    local path
    if ! path=$(find_file artisan); then
        die "You must be in a Laravel/Lumen project in order to use artisan"
        return 1
  fi
    php "$path" "$@"
}
gulp() {
    local path
    if ! path=$(find_file node_modules); then
        die "You must install gulp locally in order to use it (yarn add gulp)"
  fi
    "$path/gulp/bin/gulp.js" "$@"
}
phpspec() {
    local path
    if ! path=$(find_file vendor); then
        die "You must be in a project with phpspec in order to execute it"
  fi
    ( cd "$path/.."  && vendor/bin/phpspec "$@" )
}
get_repo_root() {
    root="$(git rev-parse --show-cdup)"
    test -n "$root"
     cd "$root" && root=$PWD
    echo ${root}
}
go_to_repo_root() {
    local path=$(get_repo_root)
    log_debug "go_to_repo_root: $path"
    go_to ${path}
    # log_current_directory
}
create_db() {
    which mysql >/dev/null  || return
    go_to_repo_root
    DB_HOST=$(sed -nr 's/DB_HOST=([^ \t]+)/\1/p' .env)
    DB_DATABASE=$(sed -nr 's/DB_DATABASE=([^ \t]+)/\1/p' .env)
    DB_USERNAME=$(sed -nr 's/DB_USERNAME=([^ \t]+)/\1/p' .env)
    DB_PASSWORD=$(sed -nr 's/DB_PASSWORD=([^ \t]+)/\1/p' .env)
    : ${DB_DATABASE:?You should set DB_DATABASE in .env}
    : ${DB_USERNAME:?You should set DB_USERNAME in .env}
    : ${DB_PASSWORD:?You should set DB_PASSWORD in .env}
    [[ $DB_HOST ]] && DB_HOST="-h$DB_HOST" || DB_HOST=""
    echo "Mysql root:"
    mysql -u root -p "$DB_HOST" "-e
        CREATE DATABASE IF NOT EXISTS \`$DB_DATABASE\` DEFAULT CHARACTER SET utf8 DEFAULT COLLATE utf8_unicode_ci;
        CREATE USER IF NOT EXISTS '$DB_USERNAME'@'%' IDENTIFIED BY '$DB_PASSWORD';
        GRANT ALL PRIVILEGES ON \`$DB_DATABASE\`.* TO '$DB_USERNAME'@'%';
        FLUSH PRIVILEGES;"
}
db_command() {
    which mysql >/dev/null  || return
    go_to_repo_root
    DB_HOST=$(sed -nr 's/DB_HOST=([^ \t]+)/\1/p' .env)
    DB_DATABASE=$(sed -nr 's/DB_DATABASE=([^ \t]+)/\1/p' .env)
    DB_USERNAME=$(sed -nr 's/DB_USERNAME=([^ \t]+)/\1/p' .env)
    DB_PASSWORD=$(sed -nr 's/DB_PASSWORD=([^ \t]+)/\1/p' .env)
    : ${DB_DATABASE:?You should set DB_DATABASE in .env}
    : ${DB_USERNAME:?You should set DB_USERNAME in .env}
    : ${DB_PASSWORD:?You should set DB_PASSWORD in .env}
    mysql "-u$DB_USERNAME" "-p$DB_PASSWORD" "$DB_HOST" "$DB_DATABASE" "$@"
}
query() {
    db_command-e "$@"
}
### END IMPORTED FROM scripts/lib/laravel_bash_helpers.sh ###### START IMPORTED FROM scripts/lib/logger_functions.sh ###
# Group: Logger Functions
# ----------------------------------------------------##!/usr/bin/env bash
# vim: syntax=sh cc=80 tw=79 ts=4 sw=4 sts=4 et sr
#######################################
# description
# Arguments:
#   1
#######################################
function log_start() {
  output_disable
  log_message "STARTING ${1}"
  log_divider
}
function log_end() {
  log_divider
  log_message "DONE WITH ${1}"
}
function log_start_of_script() {
  log_start "$script_relative_path"
}
function log_end_of_script() {
  log_end "$script_relative_path"
}
function log_start_of_function() {
  log_start "${FUNCNAME[1]}"
}
function log_end_of_function() {
  log_end "${FUNCNAME[1]}"
}
function log_divider() {
  log_message "====================================="
}
function log_current_directory() {
  log_info "Current directory is: $PWD"
}
#######################################
# description
# Arguments:
#   1 - message
#   2 - code
#   3 - caller function
#######################################
function die() {
  local msg=$1
  local code=${2:-1}
  log_error_and_stack_trace "$msg"
  kill -s TERM $TOP_PID
  # sl -e  sl: command not found
  exit $code
}
# bashsupport disable=BP2001,SpellCheckingInspection,SpellCheckingInspection
# DESC: Initialise colour variables
# ARGS: None
# OUTS: Read-only variables with ANSI control codes
# NOTE: If --no-colour was set the variables will be empty. The output of the
#       $ta_none variable after each tput is redundant during normal execution,
#       but ensures the terminal output isn't mangled when running with xtrace.
# shellcheck disable=SC2034,SC2155
function colour_init() {
  if [[ -z ${no_colour-} ]]; then
    # Text attributes
    readonly ta_bold="$(tput bold 2>/dev/null || true)"
    printf '%b' "$ta_none"
    readonly ta_uscore="$(tput smul 2>/dev/null || true)"
    printf '%b' "$ta_none"
    readonly ta_blink="$(tput blink 2>/dev/null || true)"
    printf '%b' "$ta_none"
    readonly ta_reverse="$(tput rev 2>/dev/null || true)"
    printf '%b' "$ta_none"
    readonly ta_conceal="$(tput invis 2>/dev/null || true)"
    printf '%b' "$ta_none"
    # Foreground codes
    readonly fg_black="$(tput setaf 0 2>/dev/null || true)"
    printf '%b' "$ta_none"
    readonly fg_blue="$(tput setaf 4 2>/dev/null || true)"
    printf '%b' "$ta_none"
    readonly fg_cyan="$(tput setaf 6 2>/dev/null || true)"
    printf '%b' "$ta_none"
    readonly fg_green="$(tput setaf 2 2>/dev/null || true)"
    printf '%b' "$ta_none"
    readonly fg_magenta="$(tput setaf 5 2>/dev/null || true)"
    printf '%b' "$ta_none"
    readonly fg_red="$(tput setaf 1 2>/dev/null || true)"
    printf '%b' "$ta_none"
    readonly fg_white="$(tput setaf 7 2>/dev/null || true)"
    printf '%b' "$ta_none"
    readonly fg_yellow="$(tput setaf 3 2>/dev/null || true)"
    printf '%b' "$ta_none"
    # Background codes
    readonly bg_black="$(tput setab 0 2>/dev/null || true)"
    printf '%b' "$ta_none"
    readonly bg_blue="$(tput setab 4 2>/dev/null || true)"
    printf '%b' "$ta_none"
    readonly bg_cyan="$(tput setab 6 2>/dev/null || true)"
    printf '%b' "$ta_none"
    readonly bg_green="$(tput setab 2 2>/dev/null || true)"
    printf '%b' "$ta_none"
    readonly bg_magenta="$(tput setab 5 2>/dev/null || true)"
    printf '%b' "$ta_none"
    readonly bg_red="$(tput setab 1 2>/dev/null || true)"
    printf '%b' "$ta_none"
    readonly bg_white="$(tput setab 7 2>/dev/null || true)"
    printf '%b' "$ta_none"
    readonly bg_yellow="$(tput setab 3 2>/dev/null || true)"
    printf '%b' "$ta_none"
  else
    # Text attributes
    readonly ta_bold=''
    readonly ta_uscore=''
    readonly ta_blink=''
    readonly ta_reverse=''
    readonly ta_conceal=''
    # Foreground codes
    readonly fg_black=''
    readonly fg_blue=''
    readonly fg_cyan=''
    readonly fg_green=''
    readonly fg_magenta=''
    readonly fg_red=''
    readonly fg_white=''
    readonly fg_yellow=''
    # Background codes
    readonly bg_black=''
    readonly bg_blue=''
    readonly bg_cyan=''
    readonly bg_green=''
    readonly bg_magenta=''
    readonly bg_red=''
    readonly bg_white=''
    readonly bg_yellow=''
  fi
}
# bashsupport disable=BP5006
#######################################
# colorization fix for log tables in Jenkins.  Not sure why it still prints weird characters even with color off
#######################################
function define_colors() {
  export CL_RED="\"\033[31m\""
  export CL_GRN="\"\033[32m\""
  export CL_YLW="\"\033[33m\""
  export CL_BLU="\"\033[34m\""
  export CL_MAG="\"\033[35m\""
  export CL_CYN="\"\033[36m\""
  export CL_RST="\"\033[0m\""
  # ICS has different colors
  export CL_PFX="\"\033[33m\""
  export CL_INS="\"\033[36m\""
}
function log_divider() {
  log_message "##############################################################################"
}
# DESC: Pretty print the provided string
# ARGS: $1 (required): Message to print (defaults to a green foreground)
#       $2 (optional): Colour to print the message with. This can be an ANSI
#                      escape code or one of the pre-populated colour variables.
#       $3 (optional): Set to any value to not append a new line to the message
# OUTS: None
function pretty_print() {
  if [[ $# -lt 1 ]]; then
    script_exit 'Missing required argument to pretty_print()!' 2
  fi
  if [[ -z ${no_colour-} ]]; then
    if [[ -n ${2-} ]]; then
      printf '%b' "$2: "
    else
      printf '%b' "$fg_green"
    fi
  fi
  # Print message & reset text attributes
  if [[ -n ${3-} ]]; then
    printf '%s%b' "$1" "$ta_none"
  else
    printf '%s%b\n' "$1" "$ta_none"
  fi
  echo ""
}
# DESC: Only pretty_print() the provided string if verbose mode is enabled
# ARGS: $@ (required): Passed through to pretty_print() function
# OUTS: None
function verbose_print() {
  if [[ -n ${verbose-} ]]; then
    pretty_print "$@"
  fi
}
### END IMPORTED FROM scripts/lib/logger_functions.sh ###### START IMPORTED FROM scripts/lib/permissions_functions.sh ###
# Group: Permissions Functions
# ----------------------------------------------------##!/usr/bin/env bash
# DESC: Validate we have superuser access as root (via sudo if requested)
# ARGS: $1 (optional): Set to any value to not attempt root access via sudo
# OUTS: None
function check_superuser() {
  local superuser
  if [[ $EUID -eq 0 ]]; then
    superuser=true
  elif [[ -z ${1-} ]]; then
    if check_binary sudo; then
      verbose_print 'Sudo: Updating cached credentials ...'
      if ! sudo -v; then
        verbose_print "Sudo: Couldn't acquire credentials ..." \
          "${fg_red-}"
      else
        local test_euid
        test_euid="$(sudo -H -- "$BASH" -c 'printf "%s" "$EUID"')"
        if [[ $test_euid -eq 0 ]]; then
          superuser=true
        fi
      fi
    fi
  fi
  if [[ -z ${superuser-} ]]; then
    verbose_print 'Unable to acquire superuser credentials.' "${fg_red-}"
    return 1
  fi
  verbose_print 'Successfully acquired superuser credentials.'
  return 0
}
# DESC: Run the requested command as root (via sudo if requested)
# ARGS: $1 (optional): Set to zero to not attempt execution via sudo
#       $@ (required): Passed through for execution as root user
# OUTS: None
function run_as_root() {
  output_disable
  if [[ $# -eq 0 ]]; then
    script_exit 'Missing required argument to run_as_root()!' 2
  fi
  if [[ ${1-} =~ ^0$ ]]; then
    local skip_sudo=true
    shift
  fi
  if [[ $EUID -eq 0 ]]; then
    "$@"
  elif [[ -z ${skip_sudo-} ]]; then
    sudo -H -- "$@"
  else
    script_exit "Unable to run requested command as root: $*" 1
  fi
}
#######################################
# description
# Globals:
#   EUID
#   USER
# Arguments:
#  None
#######################################
function no_root() {
  output_disable
  if [[ $EUID -eq 0 ]]; then
    die "Do not run this as the root user.  You are: $USER"
  fi
}
#######################################
# description
# Globals:
#   USER
# Arguments:
#  None
#######################################
function root_only() {
  output_disable
  if [ "x$(id -u)" != 'x0' ]; then
    die "This script can only be executed by root and you are: $USER"
  fi
}
#######################################
# Usage: set_permissions PATH USER GROUP
# Arguments:
#   1 PATH
#   2 USER
#   3 GROUP
#######################################
function permissions_general() {
  log_start_of_function
  local path=$(validate_param "${1:-}" "PATH" 1)
  local username=$(validate_param "${2:-}" "USER" 2)
  local group=$(validate_param "${3:-$username}" "GROUP" 3)
  log_info "Setting $path file permissions 644 and folder 755 for user $username and group $group..."
  output_enable
  #sudo find "$path" -exec chown "$user":"$group" {} \;
  sudo chown -R $username:$group $path
  set_directory_permissions_recursive  "$path"
  # sudo find "/home/vagrant" -type d -exec chmod 755 {} \;
  set_file_permissions_recursive "$path" 644
  output_disable
  log_end_of_function
}
function permissions_for_dotfiles(){
  log_start_of_function
  die "this doesn't work.  It affects a ton of files that shouldn't be 644. just set the ones that need it specifically"
  local username=$(validate_param "${1:-}" "username" 1)
  local dest_home=$(validate_param "${2:-}" "path to home. i.e. /root or /home/ubuntu" 2)
  sudo chown $username:$username -R $dest_home/.[^.]*
  sudo chmod 644 -R $dest_home/.[^.]*
  output_disable
  log_end_of_function
}
function fix_user_ini(){
  log_start_of_function
  local repo_root=$(validate_param "${1:-$QM_API}" "repo_root" 1)
  output_enable
  sudo chattr -i ./.user.ini || true
  sudo chattr -i $repo_root/.user.ini || true
  sudo chattr -i $repo_root/public/.user.ini || true
  sudo chattr -i /www/wwwroot/panel_ssl_site/.user.ini || true
  output_disable
  log_end_of_function
}
#######################################
# Usage: permissions_for_laravel PATH USER GROUP
# Arguments:
#   1 PATH
#   2 USER
#   3 GROUP
#######################################
function permissions_for_laravel() {
  log_start_of_function
  no_root
  go_to $1
  # TOO SLOW permissions_general "$1" "$2" "$3"
  fix_user_ini $QM_API
  output_enable
  sudo chgrp -R $NGINX_USER .
  sudo chown -R $USER .
  # bashsupport disable=BP2001
  writable_folders="storage bootstrap/cache tests/StagingUnitTests"
  # shellcheck disable=SC2086
  #sudo chgrp -R $NGINX_USER $writable_folders || true
  # shellcheck disable=SC2086
  sudo chmod -R ug+rwx storage bootstrap/cache tests/StagingUnitTests || true
  sudo chmod -R 0664 storage/logs || true
  sudo chmod 660 ".env" || true # Jenkins can't delete it for some reason
  output_disable
  log_end_of_function
  # This screws up git pulls sudo chmod -R 660 "$REPO_CONFIGS_PATH" || true
}
#######################################
# description
# Globals:
#   JENKINS_HOME
# Arguments:
#  None
#######################################
function jenkins_permissions() {
  log_start_of_function
  permissions_general "$JENKINS_HOME" $JENKINS_USER $JENKINS_USER
  jenkins_ssh_permissions
  log_end_of_function
}
function jenkins_config_permissions() {
  sudo chmod
  cd $JENKINS_HOME
  sudo find . -name "config.xml" -exec chmod 644 {} \;
  sudo find . -name "config.xml" -exec chown $JENKINS_USER:$JENKINS_USER {} \;
}
function jenkins_ssh_permissions() {
  log_start_of_function
  ssh_permissions "$JENKINS_USER" "$JENKINS_HOME"
  log_end_of_function
}
#######################################
# description
# Globals:
#   USER
# Arguments:
#   1
#######################################
function ssh_permissions() {
  # usage: permissions_ssh /home/"$USER"
  local ssh_user=$(validate_param "${1:-}" "User name" 1)
  local dest_home=$(validate_param "${2:-}" "path to home. i.e. /root or /home/ubuntu" 2)
  local ssh_folder_path=${3:-$dest_home/.ssh}
  log_info "Setting SSH file permissions for $ssh_folder_path..."
  output_enable
  sudo chown -R "${ssh_user}:${ssh_user}" "${ssh_folder_path}"
  sudo chmod 755 "${ssh_folder_path}"
  sudo chmod 600 "${ssh_folder_path}"/*
  sudo chmod 400 "${ssh_folder_path}"/config
  output_disable
}
#######################################
# description
# Globals:
#   USER
# Arguments:
#  None
#######################################
function permissions_for_home() {
  local username=$(validate_param "${1:-}" "username" 1)
  local home_folder=$(validate_param "${2:-}" "path to home. i.e. /root or /home/ubuntu" 2)
  log_start_of_function
  log_info "Setting home folder file permissions for $username at home folder $home_folder..."
  fix_user_ini $home_folder
  output_enable
  sudo chown -R $username:$username $home_folder
  set_directory_permissions_recursive "$home_folder"
  # This is too slow => sudo find "$path" -type f -exec chmod 644 {} \;
  # This is too slow  => permissions_general $home_folder $username $username
  #log_info "Only setting owner for dotfiles. Uncomment sudo chown -R \$USER ~ if you need everything, but it's slow."
  #permissions_for_dotfiles $username $home_folder
  output_disable
  permissions_general $home_folder/.oh-my-zsh $username
  permissions_general $home_folder/.composer $username || true
  ssh_permissions $username "$home_folder"
  log_end_of_function
}
function set_directory_permissions_recursive(){
  local dir=$(validate_param "${1:-}" "path to directory. i.e. /root or /home/ubuntu" 1)
  sudo find "$dir" -type d -exec chmod 755 {} \;
}
function set_file_permissions_recursive(){
  local dir=$(validate_param "${1:-}" "path to directory. i.e. /root or /home/ubuntu" 1)
  local perms=$(validate_param "${2:-}" "file permission. i.e. 644" 2)
  sudo find "$dir" -type f -exec chmod $perms {} \;
}
function set_file_owner_recursive(){
  local dir=$(validate_param "${1:-}" "path to directory. i.e. /root or /home/ubuntu" 1)
  local username=$(validate_param "${2:-}" "owner name. i.e. vagrant" 2)
  local group=$(validate_param "${3:-$username}" "GROUP" 3)
  sudo find $dir -type f -exec chown $username:$group {} \;
}
function home_dev_copy() {
  log_start_of_function
  local username=$(validate_param "${1:-}" "username" 1)
  local dest_home=$(validate_param "${2:-}" "path to home. i.e. /root or /home/ubuntu" 2)
  no_root
  output_enable
  cp -R "$HOME_DEV_CONFIGS" $dest_home
  ssh_keys_copy $username $dest_home
  output_disable
  ssh_permissions $username $dest_home
  #permissions_for_home $username $dest_home
  log_end_of_function
}
function home_global_copy() {
  log_start_of_function
  local username=$(validate_param "${1:-}" "username" 1)
  local dest_home=$(validate_param "${2:-}" "path to home. i.e. /root or /home/ubuntu" 2)
  output_enable
  rsync -a --itemize-changes "$HOME_DEV_CONFIGS/" $dest_home/
  output_disable
  #permissions_for_home $username $dest_home
  log_end_of_function
}
function my_cnf_mysql_credentials_config_copy() {
  log_start_of_function
  local src=$HOME_DEV_CONFIGS/.my.cnf
  copy_and_set_permissions_and_owner "$src" ~/$MY_CNF 600 $USER
  #copy_and_set_permissions_and_owner "$src" /home/$WSL_USER_NAME/$MY_CNF 600 $WSL_USER_NAME
  #copy_and_set_permissions_and_owner "$src" /home/ubuntu/$MY_CNF 600 ubuntu
  #copy_and_set_permissions_and_owner "$src" /var/lib/jenkins/$MY_CNF 600 jenkins
  log_end_of_function
}
function copy_and_set_permissions_and_owner(){
  local source=$(validate_param "${1:-}" "source" 1)
  local dest=$(validate_param "${2:-}" "destination" 2)
  local permissions=$(validate_param "${3:-}" "permissions" 3)
  local username=$(validate_param "${4:-}" "username" 4)
  copy "$source" $dest
  own_file $dest $username
  sudo chmod $permissions $dest
}
function own_file() {
  local file=$(validate_param "${1:-}" "destination" 1)
  local username=$(validate_param "${2:-}" "user" 2)
  sudo chown $username $file
}
function mysql_config_copy() {
  echo "Copying mysql config so that timezone is set to UTC..."
  copy "$REPO_CONFIGS_PATH"/mysql/* /etc/mysql
}
function service_restart() {
  local service=$(validate_param "${1:-}" "service name" 1)
  log_info "Restarting $service..."
  sudo service $service restart
}
function service_start_unless_running() {
  local service=$(validate_param "${1:-}" "service name" 1)
  # shellcheck disable=SC2126,SC2009
  if (( $(ps -ef | grep -v grep | grep $service | wc -l) > 0 ))
  then
      log_info "$service already running"
  else
      log_info "Starting $service..."
      service_restart $service
  fi
}
function jenkins_start() {
  fix_tmp_folder
  jenkins_ssh_permissions
  sudo mkdir /var/run/jenkins || true
  service_start_unless_running jenkins
}
function jenkins_restart() {
  fix_tmp_folder
  service_restart jenkins
}
function jenkins_install(){
  sudo mkdir /etc/ssl/certs/java/ || true
  sudo apt install --reinstall -o Dpkg::Options::="--force-confask,confnew,confmiss" --reinstall ca-certificates-java ssl-cert openssl ca-certificates
  sudo apt-get install openjdk-11-jdk
  wget -q -O - https://pkg.jenkins.io/debian-stable/jenkins.io.key | sudo apt-key add -
  sudo sh -c 'echo deb https://pkg.jenkins.io/debian-stable binary/ > \
      /etc/apt/sources.list.d/jenkins.list'
  log_info "Have to apt-get update after adding to /etc/apt/sources.list"
  apt_update
  install_unattended jenkins
}
function jenkins_restore() {
  log_start_of_function
  clone_jenkins_backup_repo
  backup_path=$JENKINS_BACKUP_REPO_PATH/var/lib/jenkins
  permissions_general $backup_path $JENKINS_USER $JENKINS_USER
  output_enable
  sudo rsync -am ${backup_path}/ $JENKINS_HOME/
  #ssh_keys_copy jenkins $JENKINS_HOME
  output_disable
  # Source: https://ostoday.org/windows/how-run-jenkins-under-another-user-in-linux.html
  #sudo cp configs/aapanel/etc/* /etc/
sudo chown -R $JENKINS_USER:$JENKINS_USER /var/log/jenkins
sudo chown -R $JENKINS_USER:$JENKINS_USER /var/lib/jenkins
sudo chown -R $JENKINS_USER:$JENKINS_USER /var/run/jenkins
sudo chown -R $JENKINS_USER:$JENKINS_USER /var/cache/jenkins
  jenkins_restart
  log_end_of_function
}
function php_ini_copy() {
  log_info "Copying /etc/php/${PHP_VERSION}/cli/php.ini so memory limit is removed for composer..."
  sudo cp "$REPO_CONFIGS_PATH/etc-global/php/${PHP_VERSION}/cli/php.ini" /etc/php/${PHP_VERSION}/cli/php.ini
}
function delete_apt_source_list(){
  log_start_of_function
  local package=$(validate_param "${1:-}" "package" 1)
  if [ -f "/etc/apt/sources.list.d/$package.list" ]; then
    log_info "Removing /etc/apt/sources.list.d/$package.list because it keeps messing up apt-get update"
    sudo rm /etc/apt/sources.list.d/$package.list
  fi
  log_end_of_function
}
function fix_broken_packages(){
  log_start_of_function
  log_info "Fixing geoclue error. You can probably remove this line"
  sudo sed -i '/geoclue/d' /var/lib/dpkg/statoverride || true
  delete_apt_source_list couchdb
  delete_apt_source_list nginx-amplify
  curl https://packages.cloud.google.com/apt/doc/apt-key.gpg | sudo apt-key --keyring /usr/share/keyrings/cloud.google.gpg add -
  apt_key_add https://cli-assets.heroku.com/apt/release.key
  log_end_of_function
}
function apt_key_add(){
	local url=$(validate_param "${1:-}" "url" 1)
  curl $url | sudo apt-key add -
  log_info "Have to apt-get update after $url | sudo apt-key add -"
  apt_update
}
function set_php_cli_version(){
  local version=${1:-${PHP_VERSION}}
  log_info "Setting PHP cli version to ${version}..."
  sudo update-alternatives --set php /usr/bin/php"${version}"
}
function php_install(){
  log_start_of_function
  non_interactive
  fix_broken_packages
  output_enable
  php -v
  log_info "installing PHP ${PHP_VERSION}..."
  apt_update
  upgrade_unattended php"${PHP_VERSION}"*
  set_php_cli_version
  log_info "Done installing PHP ${PHP_VERSION}..."
  imagemagick_install
  php -v
  php_packages=(
    php"${PHP_VERSION}"-memcached
    php"${PHP_VERSION}"-cli
    php"${PHP_VERSION}"-common
    php"${PHP_VERSION}"-curl
    php"${PHP_VERSION}"-dev
    php"${PHP_VERSION}"-fpm
    php"${PHP_VERSION}"-gd
    php"${PHP_VERSION}"-imap
    php"${PHP_VERSION}"-json
    php"${PHP_VERSION}"-mbstring
    php"${PHP_VERSION}"-mcrypt
    php"${PHP_VERSION}"-memcached
    php"${PHP_VERSION}"-mongodb
    php"${PHP_VERSION}"-mysql
    php"${PHP_VERSION}"-readline
    php"${PHP_VERSION}"-xml
    php"${PHP_VERSION}"-zip
    php-pear
  )
  for package in "${php_packages[@]}" ; do
      install_unattended "$package"
  done
  # Need to call script instead of function to make sure it runs as sudo
  sudo bash $QM_API/scripts/provision/tideways/tideways_install.sh
  xhgui_install
  log_end_of_function
}
#######################################
# description
# Globals:
#   USER
# Arguments:
#  None
#######################################
function assign_user_groups() {
  no_root
  log_info "Adding $USER to groups..."
  # usermod -a -G group user
  sudo usermod -a -G "$USER" $JENKINS_USER || true
  sudo usermod -a -G "$USER" $NGINX_USER || true
  log_info "Adding $JENKINS_USER and $NGINX_USER to $USER to group..."
  sudo usermod -a -G $JENKINS_USER "$USER" || true
  sudo usermod -a -G $NGINX_USER "$USER" || true
}
### END IMPORTED FROM scripts/lib/permissions_functions.sh ###### START IMPORTED FROM scripts/lib/php_functions.sh ###
# Group: Php Functions
# ----------------------------------------------------##!/usr/bin/env bash
# bashsupport disable=BP5001,BP5006,BP5008,BP2001
# shellcheck disable=SC2236,SC2244,SC2086,SC2248

function artisan() {
    php artisan "$@"
}
#######################################
# description
# Globals:
#   pids
# Arguments:
#  None
#######################################
# shellcheck disable=SC2236
function dusk() {
    pids=$(pidof /usr/bin/Xvfb)
    if [ ! -n "$pids" ]; then
        Xvfb :0 -screen 0 1280x960x24 &
  fi
    php artisan dusk "$@"
}

function php56() {
    set_php_cli_version 5.6
}

function php70() {
    set_php_cli_version 7.0
}

function php71() {
    set_php_cli_version 7.1
}

function php72() {
    set_php_cli_version 7.2
}

function ssh_restart() {
    log_start_of_function
    sudo service ssh --full-restart
    log_end_of_function
}
#######################################
# description
# Arguments:
#   1
#   2
#   3
#######################################
function serve-laravel() {
    if [[ $1 && $2     ]]; then
        sudo bash $HOMESTEAD_REPO/scripts/create-certificate.sh "$1"
        sudo dos2unix $HOMESTEAD_REPO/scripts/serve-laravel.sh
        sudo bash $HOMESTEAD_REPO/scripts/serve-laravel.sh "$1" "$2" 80 443 "${3:-7.1}"
  else
        log_info "Error: missing required parameters."
        log_info "Usage: "
        log_info "  serve domain path"
  fi
}
#######################################
# description
# Arguments:
#   1
#   2
#   3
#######################################
function serve-proxy() {
    if [[ $1 && $2     ]]; then
        sudo dos2unix $HOMESTEAD_REPO/scripts/serve-proxy.sh
        sudo bash $HOMESTEAD_REPO/scripts/serve-proxy.sh "$1" "$2" 80 443 "${3:-7.1}"
  else
        log_info "Error: missing required parameters."
        log_info "Usage: "
        log_info "  serve-proxy domain port"
  fi
}
#######################################
# description
# Arguments:
#   1
#   2
#   3
#######################################
function serve-silverstripe() {
    if [[ $1 && $2     ]]; then
        sudo bash $HOMESTEAD_REPO/scripts/create-certificate.sh "$1"
        sudo dos2unix $HOMESTEAD_REPO/scripts/serve-silverstripe.sh
        sudo bash $HOMESTEAD_REPO/scripts/serve-silverstripe.sh "$1" "$2" 80 443 "${3:-7.1}"
  else
        log_info "Error: missing required parameters."
        log_info "Usage: "
        log_info "  serve-silverstripe domain path"
  fi
}
#######################################
# description
# Arguments:
#   1
#   2
#   3
#######################################
function serve-spa() {
  if [[ $1 && $2     ]]; then
    sudo bash $HOMESTEAD_REPO/scripts/create-certificate.sh "$1"
    sudo dos2unix $HOMESTEAD_REPO/scripts/serve-spa.sh
    sudo bash $HOMESTEAD_REPO/scripts/serve-spa.sh "$1" "$2" 80 443 "${3:-7.1}"
  else
    log_info "Error: missing required parameters."
    log_info "Usage: "
    log_info "  serve-spa domain path"
  fi
}
#######################################
# description
# Arguments:
#   1
#   2
#   3
#######################################
function serve-statamic() {
    if [[ $1 && $2     ]]; then
        sudo bash $HOMESTEAD_REPO/scripts/create-certificate.sh "$1"
        sudo dos2unix $HOMESTEAD_REPO/scripts/serve-statamic.sh
        sudo bash $HOMESTEAD_REPO/scripts/serve-statamic.sh "$1" "$2" 80 443 "${3:-7.1}"
  else
        log_info "Error: missing required parameters."
        log_info "Usage: "
        log_info "  serve-statamic domain path"
  fi
}
#######################################
# description
# Arguments:
#   1
#   2
#   3
#######################################
function serve-symfony2() {
    if [[ $1 && $2     ]]; then
        sudo bash $HOMESTEAD_REPO/scripts/create-certificate.sh "$1"
        sudo dos2unix $HOMESTEAD_REPO/scripts/serve-symfony2.sh
        sudo bash $HOMESTEAD_REPO/scripts/serve-symfony2.sh "$1" "$2" 80 443 "${3:-7.1}"
  else
        log_info "Error: missing required parameters."
        log_info "Usage: "
        log_info "  serve-symfony2 domain path"
  fi
}
#######################################
# description
# Arguments:
#   1
#   2
#   3
#######################################
function serve-symfony4() {
    if [[ $1 && $2     ]]; then
        sudo bash $HOMESTEAD_REPO/scripts/create-certificate.sh "$1"
        sudo dos2unix $HOMESTEAD_REPO/scripts/serve-symfony4.sh
        sudo bash $HOMESTEAD_REPO/scripts/serve-symfony4.sh "$1" "$2" 80 443 "${3:-7.1}"
  else
        log_info "Error: missing required parameters."
        log_info "Usage: "
        log_info "  serve-symfony4 domain path"
  fi
}
#######################################
# description
# Arguments:
#   1
#   2
#   3
#######################################
function serve-pimcore() {
    if [[ $1 && $2     ]]; then
        sudo bash $HOMESTEAD_REPO/scripts/create-certificate.sh "$1"
        sudo dos2unix $HOMESTEAD_REPO/scripts/serve-pimcore.sh
        sudo bash $HOMESTEAD_REPO/scripts/serve-pimcore.sh "$1" "$2" 80 443 "${3:-7.1}"
  else
        log_info "Error: missing required parameters."
        log_info "Usage: "
        log_info "  serve-pimcore domain path"
  fi
}

function share() {
    if [[ "$1" ]]; then
        # shellcheck disable=SC2068
        ngrok http ${@:2} -host-header="$1" 80
  else
        log_info "Error: missing required parameters."
        log_info "Usage: "
        log_info "  share domain"
        log_info "Invocation with extra params passed directly to ngrok"
        log_info "  share domain -region=eu -subdomain=test1234"
  fi
}

function flip() {
    sudo bash $HOMESTEAD_REPO/scripts/flip-webserver.sh
}
#######################################
# description
# Arguments:
#  None
# Returns:
#   $? ...
#######################################
function has_pv() {
    # shellcheck disable=SC2091
    $(hash pv 2>/dev/null)
    return $?
}
#######################################
# description
# Arguments:
#   1
#######################################
function pv_install_message() {
    if ! has_pv; then
        log_info $1
        # shellcheck disable=SC2016
        log_info 'Install pv with `sudo apt-get install -y pv` then run this command again.'
        log_info ""
  fi
}
#######################################
# description
# Globals:
#   ADJUSTED_SIZE
#   FILE
#   HUMAN_READABLE_SIZE
#   SIZE_QUERY
# Arguments:
#   1
#######################################
function db_export() {
    FILE=${1:-$HOMESTEAD_REPO/mysqldump.sql.gz}
    # This gives an estimate of the size of the SQL file
    # It appears that 80% is a good approximation of
    # the ratio of estimated size to actual size
    SIZE_QUERY="select ceil(sum(data_length) * 0.8) as size from information_schema.TABLES"
    pv_install_message "Want to see export progress?"
    log_info "Exporting databases to '$FILE'"
    if has_pv; then
        ADJUSTED_SIZE=$(mysql --vertical -uhomestead -psecret -e "$SIZE_QUERY" 2>/dev/null | grep 'size' | awk '{print $2}')
        HUMAN_READABLE_SIZE=$(numfmt --to=iec-i --suffix=B --format="%.3f" $ADJUSTED_SIZE)
        log_info "Estimated uncompressed size: $HUMAN_READABLE_SIZE"
        mysqldump -uhomestead -psecret --all-databases --skip-lock-tables 2>/dev/null | pv  --size=$ADJUSTED_SIZE | gzip >"$FILE"
  else
        mysqldump -uhomestead -psecret --all-databases --skip-lock-tables 2>/dev/null | gzip >"$FILE"
  fi
    log_info "Done."
}
#######################################
# description
# Globals:
#   FILE
# Arguments:
#   1
#######################################
function db_import() {
    FILE=${1:-$HOMESTEAD_REPO/mysqldump.sql.gz}
    pv_install_message "Want to see import progress?"
    log_info "Importing databases from '$FILE'"
    if has_pv; then
        pv "$FILE" --progress --eta | zcat | mysql -uhomestead -psecret 2>/dev/null
  else
        # shellcheck disable=SC2002
      cat "$FILE" | zcat | mysql -uhomestead -psecret 2>/dev/null
  fi
    log_info "Done."
}
#######################################
# description
# Globals:
#   XDEBUG_ENABLED
# Arguments:
#  None
#######################################
function xdebug_cli() {
    (php -m | grep -q xdebug)
    # shellcheck disable=SC2181
    if [[ $? -eq 0 ]]; then
        XDEBUG_ENABLED=true
  else
          XDEBUG_ENABLED=false
  fi
    if ! $XDEBUG_ENABLED; then xon; fi
    php \
        -dxdebug.client_host=192.168.10.1 \
        -dxdebug.remote_autostart=1 \
        "$@"
    if ! $XDEBUG_ENABLED; then xoff; fi
}
#######################################
# Arguments:
#  None
#######################################
#######################################
# description
# Globals:
#   PHP_VERSION$QM_API
#   scripts
# Arguments:
#  None
#######################################
function production_composer_install() {
  cd "$DEPLOY_BUILD_FOLDER"
  if [ -z "${RELEASE_STAGE:-}" ]; then die 'Please set RELEASE_STAGE env'; fi
  env_copy "${RELEASE_STAGE}"
  log_info "Setting $USER as owner of build folder so composer can set phantomjs permissions..."
  sudo chown -R "$USER":"$USER" "$DEPLOY_BUILD_FOLDER"
  set_php_cli_version
  no_root
  output_commit_message_and_env
  log_process "composer install --no-dev --optimize-autoloader" "COMPOSER INSTALL"
  update_important_submodules
}
function update_important_submodules() {
    log_process "php $SCRIPTS_FOLDER/git/update_important_submodules.php" "UPDATE SUBMODULES"
}
function update_all_submodules() {
    log_process "php $SCRIPTS_FOLDER/git/update_all_submodules.php" "UPDATE SUBMODULES"
}
function log_process() {
  local cmd=$(validate_param "${1:-}" "command" 1)
  local title=${2:-$cmd}
  log_start $title
  $cmd
  log_end $title
}
#######################################
# description
# Globals:$QM_API
#   scripts
# Arguments:
#  None
#######################################
function test_composer_install() {
  go_to_repo_root
  sudo rm -rf "$QM_API/storage/charts"
  own_www_root
  #symlink_slave_folders
  log_start_of_function
  composer_install
  #aapanel_nginx_config
  log_end_of_function
}
function own_www_root() {
  fix_user_ini
  log_info "Setting $USER as owner of www root..."
  sudo chown -R "$USER":"$NGINX_USER" "$WWW_ROOT"
}
function ssh_keys_copy(){
  log_start_of_function
  local username=$(validate_param "${1:-}" "username" 1)
  local dest_home=$(validate_param "${2:-}" "path to home. i.e. /root or /home/ubuntu" 2)
  local ssh_folder_path=$dest_home/.ssh
  log_message "Copying ssh keys because setting permissions in repo messes up git clone"
  output_enable
  sudo mkdir $ssh_folder_path || true
  sudo cp -R "$SSH_KEYS"/* $ssh_folder_path
  set_file_permissions_recursive "$ssh_folder_path" 600
  set_file_owner_recursive $ssh_folder_path $username
  sudo chmod 644 "$ssh_folder_path"/authorized_keys
  output_disable
  log_end_of_function
}
function aapanel_nginx_config(){
  log_start_of_function
  output_enable
  # No such directory
  #sudo wget -O /etc/pki/tls/certs/ca-bundle.crt https://curl.se/ca/cacert.pem
  #sudo chmod 444 /etc/pki/tls/certs/ca-bundle.crt
  sudo rm /www/server/panel/vhost/nginx/*
  sudo cp -R $QM_API/configs/aapanel/www/server/panel/vhost/* /www/server/panel/vhost
  permissions_for_laravel "$QM_API"
  restart_services_aapanel
  output_disable
  log_end_of_function
}
function aapanel_nginx_config_phpunit(){
  log_start_of_function
  output_enable
  # No such directory
  #sudo wget -O /etc/pki/tls/certs/ca-bundle.crt https://curl.se/ca/cacert.pem
  #sudo chmod 444 /etc/pki/tls/certs/ca-bundle.crt
  local nginx_path=/www/server/panel/vhost/nginx
  sudo rm $nginx_path/*.conf
  sudo cp $QM_API/configs/aapanel$nginx_path/qm-api.conf $nginx_path/qm-api.conf
  permissions_for_laravel "$QM_API"
  restart_services_aapanel
  output_disable
  log_end_of_function
}
function composer_install() {
  log_start_of_function
  export COMPOSE_INTERACTIVE_NO_CLI=1 # this fixes the input device is not a TTY .. see https://github.com/docker/compose/issues/5696
  no_root
  go_to_repo_root
  output_enable
  composer install
  log_end_of_function
}
#######################################
# description
# Globals:$QM_API
#   SUCCESS_FILE
#   exit_code
# Arguments:
#  None
#######################################
function db_migrate() {
  output_enable
  go_to_repo_root
  php artisan migrate --force --database=migrations
  log_start "Update Database Constants"
  local success_file=update_database_constants_success
  touch ${success_file}
  assert_file_exists ${success_file} "Update Database Constants FAILED"
  php database/update_database_constants.php
  assert_file_does_not_exist ${success_file} "php database/update_database_constants.php failed!"
}
#######################################
#  add_envs "$QM_API/.env"
# Arguments:
#   1 - env file path
#######################################
function restart_services() {
  log_start_of_function
  sudo service filebeat restart || true
  restart_databases
  restart_web_services
  log_end_of_function
}
function restart_databases() {
  log_info "Restarting databases..."
  mysql_restart
  service_restart redis-server
}
function mysql_restart(){
  fix_tmp_folder
  sudo service mysql stop
  sudo usermod -d /var/lib/mysql/ mysql
  sudo service mysql start
}
function start_databases() {
  service_start_unless_running redis-server
  service_start_unless_running mysql
}
function sail_up_background() {
  log_start_of_function
  cd $QM_API && sail up -d
  log_end_of_function
}
function sail_up_interactive() {
  log_start_of_function
  cd $QM_API && sail up
  log_end_of_function
}
function restart_services_aapanel() {
  log_start_of_function
  output_enable
  #folder_to_link /home/ubuntu/qm-api /www/wwwroot/qm-api || true
  sudo /etc/init.d/mysqld restart
  sudo /etc/init.d/redis restart
  sudo /etc/init.d/nginx restart
  sudo /etc/init.d/memcached restart
  sudo /etc/init.d/mongodb restart
  output_disable
  log_end_of_function
}
function restart_web_services() {
  log_info "Restarting web services..."
  service_restart nginx
  sudo nginx -t 2>/dev/null >/dev/null
  # shellcheck disable=SC2181
  if [[ $? == 0 ]]; then
    log_info "Valid nginx config"
  else
    log_info "Invalid nginx config"
    sudo nginx -t
    die "Invalid nginx config"
  fi
  sudo mkdir /run/php || true
  service_restart php"${PHP_VERSION}"-fpm
  sudo php-fpm"${PHP_VERSION}" -t 2>/dev/null >/dev/null
  # shellcheck disable=SC2181
  if [[ $? == 0 ]]; then
    log_info "Valid php-fpm${PHP_VERSION} config"
  else
    log_info "Invalid php${PHP_VERSION}-fpm"
    sudo php-fpm"${PHP_VERSION}" -t
    die "Invalid php${PHP_VERSION}-fpm"
  fi
}
### END IMPORTED FROM scripts/lib/service_functions.sh ###### START IMPORTED FROM scripts/lib/time_functions.sh ###
# Group: Time Functions
# ----------------------------------------------------##!/usr/bin/env bash
#######################################
# variable_name="$(datetime)"
#######################################
function datetime() {
  output_disable
  # bashsupport disable=BP5006
  local datetime=$(date +%Y-%m-%d--%H-%M-%S)
  echo "$datetime"
}

function date_time() {
    date +"%Y/%m/%d %H:%M:%S"
}

function utc_date_time() {
    date -u +"%Y/%m/%dT%H:%M:%SZ"
}
### END IMPORTED FROM scripts/lib/time_functions.sh ###### START IMPORTED FROM scripts/lib/validation_functions.sh ###
# Group: Validation Functions
# ----------------------------------------------------##!/usr/bin/env bash
#######################################
# usage: validate_variable_set "GITHUB_ACCESS_TOKEN"
# Arguments:
#   1 Name i.e. "GITHUB_ACCESS_TOKEN"
#   2 - Optional message
#######################################
function validate_variable_set() {
  output_disable
  local variable_name="${1}"
  local message=${2:-"No error message provided to validate_variable_set"}
   get_variable_value_by_name $variable_name ${message}
}
function get_variable_value_by_name() {
  declare -n var_name=$1
  local message=${2:-"No error message provided to get_variable_value_by_name"}
  if [[ -v var_name ]]; then
    log_debug "${!var_name} is ${var_name}" # be careful with this because it will log secrets
  else
    die "Please set variable with name <${!var_name}> before running this script! ${message}"
  fi
}
#######################################
# usage:
#	local name=$(validate_param "${1:-}" "name" 1)
#	local value=$(validate_param "${2:-}" "value" 2)
#	local file=$(validate_param "${3:-}" "file" 3)
# Arguments:
#   1 Value i.e. "${1}"
#   2 Name i.e. "PATH to ssh parent folder (i.e. permissions_ssh /home/$USER)"
#   3 Parameter number i.e. 1
# Live Template: validate_param
#######################################
function validate_param() {
  output_disable
  #  log_debug "validate_param:
  #    VALUE: $1
  #    NAME: $2
  #    PARAM: $3"
  if [[ -z ${1} ]]; then die "Please provide $2 as parameter $3, ya dummy!"; fi
  #log_info $1
  echo ${1}
}
function relative_path() {
  get_string_after_substring $1 "$QM_API/"
}
function explode_path() {
  local parts=$(explode $1 "/")
  echo $parts
}
function path_to_file_name() {
  local abs_path=$1
   local file_name="$(basename $abs_path)"
    echo $file_name
}
function log_frame(){
  local caller_info=$(validate_param "${1[@]:-}" "caller_info" 1)
  local frame=$(validate_param "${2:-}" "frame" 2)
  local argv=$(validate_param "${3[@]:-}" "argv" 3)
   abs_path_maybe=${caller_info[2]}
   file_name=$(path_to_file_name ${abs_path_maybe})
   line_number=${caller_info[0]}
   caller_func=${caller_info[1]}
   called_func=${FUNCNAME[frame]}
   log_message ":: ${file_name}:${line_number} ${caller_func} -> ${called_func}(${argv[*]})"
}
function get_current_function_name(){
  return ${FUNCNAME[1]}
}
# shellcheck disable=SC2249,SC2004,SC2207,SC2034,SC2034
stacktrace()
{
   declare frame=0
   declare argv_offset=0
   while caller_info=( $(caller $frame) ) ; do
       if shopt -q extdebug ; then
           declare argv=()
           declare argc
           declare frame_argc
           for ((frame_argc=${BASH_ARGC[frame]},frame_argc--,argc=0; frame_argc >= 0; argc++, frame_argc--)) ; do
               argv[argc]=${BASH_ARGV[argv_offset+frame_argc]}
               case "${argv[argc]}" in
                   *[[:space:]]*) argv[argc]="'${argv[argc]}'" ;;
               esac
           done
           log_frame "${caller_info[@]}" $frame "${argv[@]}"
       fi
       frame=$((frame+1))
   done
   if [[ $frame -eq 1 ]] ; then
       caller_info=( $(caller 0) )
       log_message ":: ${caller_info[2]}: Line ${caller_info[0]}: ${caller_info[1]}"
   fi
}
# Throws error - if it is.
#
# @example: exitIfError $? "Your error text".
# @example: exitIfError $1 "Your error text".
function exit_if_error() {
  local exit_code=$1
  # shellcheck disable=SC2244,SC2145
  [[ $exit_code ]] \
                   && ((exit_code != 0)) && {
      die "$@" "$exit_code"
  }
}
### END IMPORTED FROM scripts/lib/validation_functions.sh ###
#######################################
# Avoid getting asked about overwriting config files
# Fixes dpkg-preconfigure: unable to re-open stdin: No such file or directory
#######################################
function non_interactive() {
  # bashsupport disable=BP5006,BP2001
  export DEBIAN_FRONTEND=noninteractive
}
function upgrade_unattended(){
  log_start_of_function
  non_interactive
  local package=$(validate_param "${1:-}" "package" 1)
  log_info "Upgrading $package and keeping current configuration file. The new version is installed with a .dpkg-dist suffix if you need it. "
  # --force-confold: do not modify the current configuration file, the new version is installed with a .dpkg-dist suffix. With this option alone, even configuration files that you have not modified are left untouched. You need to combine it with --force-confdef to let dpkg overwrite configuration files that you have not modified.
  apt_update_if_necessary
  sudo apt-get install --only-upgrade -y -o Dpkg::Options::="--force-confdef" -o Dpkg::Options::="--force-confold" "$package" >/dev/null
  #log_end "installing $package"
  log_end_of_function
}
function install_unattended(){
  log_start_of_function
  non_interactive
  local package=$(validate_param "${1:-}" "package" 1)
  log_info "Installing $package and keeping current configuration file. The new version is installed with a .dpkg-dist suffix if you need it. "
  # --force-confold: do not modify the current configuration file, the new version is installed with a .dpkg-dist suffix. With this option alone, even configuration files that you have not modified are left untouched. You need to combine it with --force-confdef to let dpkg overwrite configuration files that you have not modified.
  apt_update_if_necessary
  output_enable
  sudo apt-get install -y -o Dpkg::Options::="--force-confdef" -o Dpkg::Options::="--force-confold" "$package"
  output_disable
  #log_end "installing $package"
  log_end_of_function
}
function output_enable() {
    { set -x; } 2>/dev/null
}
function output_disable() {
    { set +x; } 2>/dev/null
}
# Return true (0) if the first string (haystack) contains the second string (needle), and false (1) otherwise.
function string_contains() {
  local haystack="$1"
  local needle="$2"
  [[ $haystack == *"$needle"*   ]]
}
# Returns true (0) if the first string (haystack), which is assumed to contain multiple lines, contains the second
# string (needle), and false (1) otherwise. The needle can contain regular expressions.
function string_multiline_contains() {
  local -r haystack="$1"
  local -r needle="$2"
  echo "$haystack" | grep -q "$needle"
}
# Convert the given string to uppercase
# bashsupport disable=SpellCheckingInspection
function string_to_uppercase() {
  local -r str="$1"
  echo "$str" | awk '{print toupper($0)}'
}
function get_string_after_substring() {
    local -r str="$1"
    local -r prefix="$2"
    string_strip_prefix $str "*$prefix"
}
# Strip the prefix from the given string. Supports wildcards.
#
# Example:
#
# string_strip_prefix "foo=bar" "foo="  ===> "bar"
# string_strip_prefix "foo=bar" "*="    ===> "bar"
#
# http://stackoverflow.com/a/16623897/483528
function string_strip_prefix() {
  local -r str="$1"
  local -r prefix="$2"
  echo "${str#$prefix}"
}
# Strip the suffix from the given string. Supports wildcards.
#
# Example:
#
# string_strip_suffix "foo=bar" "=bar"  ===> "foo"
# string_strip_suffix "foo=bar" "=*"    ===> "foo"
#
# http://stackoverflow.com/a/16623897/483528
function string_strip_suffix() {
  local -r str="$1"
  local -r suffix="$2"
  echo "${str%$suffix}"
}
# Return true if the given response is empty or "null" (the latter is from jq parsing).
function string_is_empty_or_null() {
  local -r response="$1"
  [[ -z $response || $response == "null"     ]]
}
# Given a string $str, return the substring beginning at index $start and ending at index $end.
#
# Example:
#
# string_substr "hello world" 0 5
#   Returns "hello"
# shellcheck disable=SC2016
function string_substr() {
  local -r str="$1"
  local -r start="$2"
  local end="$3"
  if [[ $start -lt 0 || $end -lt 0     ]]; then
    log_error_and_stack_trace 'In the string_substr bash function, each of $start and $end must be >= 0.'
    exit 1
  fi
  if [[ $start -gt $end     ]]; then
    # shellcheck disable=SC2016
    log_error_and_stack_trace 'In the string_substr bash function, $start must be < $end.'
    exit 1
  fi
  if [[ -z $end   ]]; then
    end="${#str}"
  fi
  echo
}
function delete_and_recreate_directory() {
  # bashsupport disable=BP5006
  local dir=$(validate_param "$1" "dir" 1)
  local message="${2:-}"
  log_info "Deleting and creating $dir folder $message"
  rm "${dir:?}/*" || true
  rm -rf "${dir:?}/" || true
  mkdir "$dir" || true
}
#######################################
# description
#######################################
function delete_folder() {
  local folder=$(validate_param "${1:-}" "folder to delete" 1)
  log_info "deleting folder: $folder"
  rm -r "$folder" || true
}
function symlink() {
    folder_to_link $1 $2
}
function symlink_wsl(){
  log_start_of_function
  symlink /www/wwwroot/qm-api /qm-api
  symlinks_global
  log_end_of_function
}
function symlinks_global(){
  log_start_of_function
  symlink /var/lib/jenkins /jenkins
  symlink /etc/nginx /nginx
  symlink /etc/php /php
  symlink /var/log /log
  log_end_of_function
}
#######################################
# description
#######################################
function replace_folder_with_symlink() {
  local folder_to_delete=$(validate_param "${1:-}" "folder to delete" 1)
  local folder_to_link_to=$(validate_param "${2:-}" "folder to link to" 2)
    delete_folder "$folder_to_delete"
    folder_to_link $folder_to_link_to $folder_to_delete
}
#######################################
# description
#######################################
function replace_qm_folder_with_symlink() {
    local relative_path=$(validate_param "${1:-}" "relative_path" 1)
    local shared="$QM_API_SHARED/$relative_path"
    mkdir "$shared" || true
    replace_folder_with_symlink "$QM_API/$relative_path" "$shared"
}
function ssh_setup() {
  local ssh_user=${1:-${USER}}
  local dest_home=$(validate_param "${2:-}" "path to home. i.e. /root or /home/ubuntu" 2)
  local ssh_folder_path=${dest_home}/.ssh
  log_info "Using current user as SSH_USER: ${ssh_user}"
  log_info "Using SSH_PATH: ${ssh_folder_path}"
  sudo mkdir "${ssh_folder_path}" || true
  ssh_keys_copy $ssh_user $dest_home
  sudo systemctl restart sshd
  log_divider
  log_info "Setup SSH keys for ${ssh_user}"
  log_info "Login with as root on port 2223 with $SSH_KEYS/id_rsa"
  log_divider
}
function mysql57_setup() {
  log_info "Install MySQL"
  echo "mysql-server mysql-server/root_password password $PW" | debconf-set-selections
  echo "mysql-server mysql-server/root_password_again password $PW" | debconf-set-selections
  apt-get install -y mysql-server
  log_info "Configure MySQL Password Lifetime"
  echo "default_password_lifetime = 0" >>/etc/mysql/mysql.conf.d/mysqld.cnf
  log_info "Configure MySQL Remote Access..."
  mysql --user="root" --password="$PW" -e "GRANT ALL ON *.* TO root@'0.0.0.0' IDENTIFIED BY '$PW' WITH GRANT OPTION;"
  service mysql restart
  mysql --user="root" --password="$PW" -e "CREATE USER '$WSL_USER_NAME'@'0.0.0.0' IDENTIFIED BY '$PW';"
  mysql --user="root" --password="$PW" -e "GRANT ALL ON *.* TO '$WSL_USER_NAME'@'0.0.0.0' IDENTIFIED BY '$PW' WITH GRANT OPTION;"
  mysql --user="root" --password="$PW" -e "GRANT ALL ON *.* TO '$WSL_USER_NAME'@'%' IDENTIFIED BY '$PW' WITH GRANT OPTION;"
  mysql --user="root" --password="$PW" -e "FLUSH PRIVILEGES;"
  mysql --user="root" --password="$PW" -e "CREATE DATABASE homestead character set UTF8mb4 collate utf8mb4_bin;"
}
function clean_folder() {
  delete_folder $1
  mkdir -p $1
}
function phpunit() {
  log_lightsail_links
  junit_folder=$QM_API/build
  PHPUNIT_J_UNIT_FILE="$junit_folder/junit.xml"
  local test_path=$(validate_param "${1:-${TEST_PATH:-}}" "TEST_PATH" 1)
  go_to_repo_root
  clean_folder $junit_folder
  output_enable
  #PHPUNIT_OPTIONS="--stop-on-error --stop-on-failure --log-junit $PHPUNIT_J_UNIT_FILE"
  PHPUNIT_OPTIONS=" --log-junit $PHPUNIT_J_UNIT_FILE"
  printenv
  vendor/phpunit/phpunit/phpunit --configuration phpunit.xml $PHPUNIT_OPTIONS $test_path
  output_disable
  assert_file_exists "$PHPUNIT_J_UNIT_FILE"
  log_info "Touching $PHPUNIT_J_UNIT_FILE to deal with Clock on this slave is out of sync with the master error"
  touch $PHPUNIT_J_UNIT_FILE
}
#######################################
# description
# Globals:
#   WORKSPACE
#   folder
#   script_relative_path
# Arguments:
#   1
#######################################
function symlink_slave_folders() {
  log_start_of_function
  no_root
  if variable_is_empty WORKSPACE; then
    log_info "Skipping $script_relative_path because WORKSPACE is not set"
    log_environment
  else
      git_clone mikepsinn/qm-api develop $QM_API_SHARED
       log_info "Replacing folders with symlinks to reduce duplicate file storage on slaves"
       local folders=(
          tmp
          storage
          vendor
          log
          public/dev
          public/qm-application-settings
          public/dev-docs
    )
    for folder in "${folders[@]}"; do
        local shared="$QM_API_SHARED/$folder"
        mkdir "$shared" || true
        replace_folder_with_symlink "$WORKSPACE/$folder" "$shared"
        sudo chown -R $USER "$folder"
    done
  fi
  log_end_of_function
}
#######################################
# description
# Globals:
#   GITHUB_ACCESS_TOKEN
# Arguments:
#   1 owner/repo i.e. mikepsinn/jenkins-backup
#   2 branch i.e. master
#   3 destination
# ex: git_clone mikepsinn/jenkins-backup master $JENKINS_BACKUP_REPO
#######################################
function git_clone() {
  log_start_of_function
  local owner_repo=$(validate_param "${1:-}" "owner/repo i.e. mikepsinn/jenkins-backup" 1)
  local branch=$(validate_param "${2:-}" "branch i.e. master" 2)
  local destination=$(validate_param "${3:-}" "destination" 3)
  if folder_exists "$destination"; then
    log_warning "Not cloning $owner_repo because folder $destination already exists"
    return 0
  fi
  git_config
  output_disable
  log_info "Cloning ${owner_repo} to $destination..."
# TODO: Start using    git clone -b $branch https://@github.com/${owner_repo}.git "$destination" || true
  git clone -b $branch "https://$(github_access_token)@github.com/${owner_repo}.git" "$destination" || true
  output_disable
  log_end_of_function
}
function git_clone_sha(){
  log_start_of_function
  local owner_repo=$(validate_param "${1:-}" "owner/repo i.e. mikepsinn/jenkins-backup" 1)
  local sha=$(validate_param "${2:-}" "sha" 2)
  local destination=$(validate_param "${3:-}" "destination" 3)
  mkdir $destination || true
  cd $destination
  git remote add origin "https://$(github_access_token)@github.com/${owner_repo}.git"
  log_info "fetching ${owner_repo} commit sha $sha..."
  # Note: the full history up to this commit will be retrieved unless you limit it with '--depth=...' or '--shallow-since=...'
  git fetch origin $sha --depth=10
  # reset this repository's master branch to the commit of interest
  git reset --hard FETCH_HEAD
  log_end_of_function
}
function git_clone_sha_remote(){
  log_start_of_function
  local owner_repo=$(validate_param "${1:-}" "owner/repo i.e. mikepsinn/jenkins-backup" 1)
  local sha=$(validate_param "${2:-}" "sha" 2)
  local destination=$(validate_param "${3:-}" "destination" 3)
  local ip=$(validate_param "${4:-}" "ip" 4)
  local git_url="https://$(github_access_token)@github.com/${owner_repo}.git"
  ssh_command $ip "git clone --depth 1 ${git_url} $destination || true"
  ssh_command $ip "cd $destination && git remote add origin ${git_url} || true"
  log_info "fetching ${owner_repo} commit sha $sha..."
  # Note: the full history up to this commit will be retrieved unless you limit it with '--depth=...' or '--shallow-since=...'
  ssh_command $ip "cd $destination && git fetch origin ${sha} --depth=10"
  # reset this repository's master branch to the commit of interest
  ssh_command $ip "cd $destination && git reset --hard FETCH_HEAD"
  log_end_of_function
}
#######################################
# description
# Arguments:
#   1 repo path
#######################################
function git_stash_reset_and_pull() {
  local branch=$(validate_param "${1:-}" "branch" 1)
  local destination=$(validate_param "${2:-}" "destination" 1)
  go_to $destination
  git stash
  git reset --hard
  git fetch
  git checkout "$branch"
  git pull
}
function go_to(){
  local path=$(validate_param "${1:-}" "path" 1)
  log_info "Going to $path..."
  cd "$path" || die "Folder $path not found"
}
#######################################
# description
# Globals:
#   GITHUB_ACCESS_TOKEN
# Arguments:
#   1 owner/repo i.e. mikepsinn/jenkins-backup
#   2 branch i.e. master
#   3 destination
# ex: git_clone mikepsinn/jenkins-backup master $JENKINS_BACKUP_REPO
#######################################
function git_clone_or_pull() {
  local owner_repo=$(validate_param "${1:-}" "owner/repo i.e. mikepsinn/jenkins-backup" 1)
  local branch=$(validate_param "${2:-}" "branch i.e. master" 2)
  local destination=$(validate_param "${3:-}" "destination" 3)
  git_clone $owner_repo $branch $destination
  git_stash_reset_and_pull $branch $destination
}
function clone_or_pull_ionic(){
  git_clone_or_pull "QuantiModo/quantimodo-android-chrome-ios-web-app" "develop" $IONIC_PATH
}
#######################################
# description
# Globals:
#   GITHUB_ACCESS_TOKEN
# Arguments:
#   1 path
#   2 branch i.e. master
#   3 message
# ex: git_clone mikepsinn/jenkins-backup master $JENKINS_BACKUP_REPO
#######################################
function git_commit_all(){
  local path=$(validate_param "${1:-}" "repo path" 1)
  local local_branch=$(validate_param "${2:-}" "local branch i.e. master" 2)
  local message=$(validate_param "${3:-}" "message" 3)
  local remote_branch=${4:-$local_branch}
  go_to "${path}"
  git add .
  git commit -m "$message"
  git push origin $local_branch:$remote_branch
}
# Join arguments with delimiter
# @Params
# $1: The delimiter string
# ${@:2}: The arguments to join
# @Output
# >&1: The arguments separated by the delimiter string
function explode() {
  (($#)) || die "At least delimiter required"
  local -- delim="$1" str IFS=
  shift
  str="${*/#/$delim}" # Expand arguments with prefixed delimiter (Empty IFS)
  echo "${str:${#delim}}" # Echo without first delimiter
}
function clone_jenkins_backup_repo(){
  git_clone_or_pull $JENKINS_BACKUP_REPO_NAME master $JENKINS_BACKUP_REPO_PATH
  go_to "${JENKINS_BACKUP_REPO_PATH}"
  backup_folder="${JENKINS_BACKUP_REPO_PATH}/var/lib/jenkins"
  go_to "${backup_folder}"
  find . -name '*nextBuildNumber' -delete
  find . -name '*lastStable' -delete
  find . -name '*lastSuccessful' -delete
  find . -name '*github-polling.log' -delete
}
function rsync_folder_without_permissions_including_deletion() {
  local src=$(validate_param "${1:-}" "src i.e. /var/lib/jenkins" 1)
  local dest=$(validate_param "${2:-}" "dest i.e. /backup-folder" 2)
  local exclusion_str=$(validate_param "${3:-}" "string of files to from rsync_exclude_string function " 3)
  # shellcheck disable=SC2086
  rsync_folder "${src}" "${dest}" "${exclusion_str}" "-am --checksum --stats --omit-dir-times --no-perms --no-owner --no-group --delete"
}
function rsync_folder_with_permissions_including_deletion() {
  # shellcheck disable=SC2086
  rsync_folder "${1:-}" "${2:-}" "${3:-}" "-r --stats --delete"
}
function rsync_exclude_string() {
  excluded=("$@")
  local exclude_str=""
  for index in ${!excluded[*]}
  do
      exclude_str="$exclude_str --exclude ${excluded[$index]}"
  done
  echo $exclude_str;
}
function rsync_folder() {
  log_start_of_function
  local src=$(validate_param "${1:-}" "src i.e. /var/lib/jenkins" 1)
  local dest=$(validate_param "${2:-}" "dest i.e. /backup-folder" 2)
  local exclusion_str=$(validate_param "${3:-}" "string of files to from rsync_exclude_string function " 3)
  local opts=$(validate_param "${4:-}" "rsync options" 4)
  # shellcheck disable=SC2086
  output_enable
  sudo rsync ${opts} ${exclusion_str} $src/ ${dest}/
  output_disable
  log_end_of_function
}
function rsync_to_web_server(){
  local remote_host=$(validate_param "${1}" "HOSTNAME" 1)
  local remote_user=$(validate_param "${2:-ubuntu}" "remote_user" 2)
  local source_dir=${WORKSPACE}
  local destination_dir=${DEPLOY_BUILD_FOLDER:-/home/ubuntu/qm-api}
  rsync_remote ${remote_host} ${remote_user} ${source_dir} "${destination_dir}"
}
function rsync_files_non_recursive_including_deletion() {
  log_start_of_function
  local src=$(validate_param "${1:-}" "src i.e. /var/lib/jenkins" 1)
  local dest=$(validate_param "${2:-}" "dest i.e. /backup-folder" 2)
  output_enable
  sudo rsync -a -f"- */" -f"+ *" ${src}/ ${dest}/ --delete
  log_end_of_function
}
function own_folder(){
  no_root
  local folder=$(validate_param "${1:-}" "folder path" 1)
  output_enable
  sudo chown -R "$USER":"$USER" "$folder"
  output_disable
}
function own_workspace() {
  log_start_of_function
  log_info "Owning folder to avoid permissions issues when Jenkins tries to run again"
  sudo chown -R $USER $WORKSPACE
  log_end_of_function
}
function output_env_value(){
  validate_variable_set "$1"
  local value=$(get_variable_value_by_name "$1")
  log_info "$1 is $value"
}
function apt_update(){
  log_info "Running sudo apt-get update -y -qq..."
  sudo apt-get update -y -qq
  export APT_GET_UPDATED=1
}
function apt_update_if_necessary(){
  if [ -z "${APT_GET_UPDATED:-}" ]; then
    apt_update
  else
    log_debug 'Already ran apt-get update'
  fi
}
function apt_upgrade(){
  log_info "Running sudo apt -y upgrade..."
  sudo sudo apt -y upgrade
}
function xdebug_cli(){
  local cmd=$(validate_param "${1:-}" "command to debug" 1)
  /usr/bin/php${PHP_VERSION} -dxdebug.mode=debug -dxdebug.client_port=9000 -dxdebug.client_host=127.0.0.1 $cmd
}
export DEPLOY_USER=ubuntu
export DEPLOY_HOME=/home/$DEPLOY_USER
export DEPLOY_BUILD_FOLDER=$DEPLOY_HOME/qm-api
export DEPLOY_RELEASES_BASE=$DEPLOY_HOME/releases
function deploy_release_folder(){
  if [ -z "${GIT_COMMIT:-}" ]; then set_git_envs; fi
  export DEPLOY_RELEASE_FOLDER="$DEPLOY_RELEASES_BASE/$GIT_COMMIT"
  log_info "DEPLOY_RELEASE_FOLDER is ${DEPLOY_RELEASE_FOLDER}"
  echo $DEPLOY_RELEASE_FOLDER
}
function ssh_command() {
	local ip=$(validate_param "${1:-}" "ip" 1)
	local cmd=$(validate_param "${2:-}" "cmd" 2)
	log_info "Running ssh $DEPLOY_USER@${ip} $cmd"
	output_enable
  # shellcheck disable=SC2029
  ssh $DEPLOY_USER@${ip} "$cmd"
  output_disable
}
function trigger_deploy_on_web_servers(){
  log_start_of_function
  # Why is this necessary here?  home_dev_copy
  set_git_envs
  export release_folder=$(deploy_release_folder)
  validate_variable_set IPS
  validate_variable_set RELEASE_STAGE
  local prefix="export RELEASE_STAGE=$RELEASE_STAGE && cd $DEPLOY_BUILD_FOLDER &&"
  for ip in "${IPS[@]}"; do
    git_clone_sha_remote mikepsinn/qm-api $GIT_COMMIT "$DEPLOY_BUILD_FOLDER" $ip
    ssh_command $ip "$prefix bash scripts/deploy/build_on_web_server.sh";
  done
  for ip in "${IPS[@]}"; do
    ssh_command ${ip} "$prefix bash scripts/deploy/sync_to_release_folder.sh";
  done
  log_end_of_function
}
function deploy_from_remote_sever(){
  jenkins_ssh_permissions
  validate_variable_set RELEASE_STAGE RELEASE_STAGE
  validate_variable_set ips "IP's to deploy to"
  # shellcheck disable=SC2153
  env_copy "${RELEASE_STAGE}"-remote
  #production_composer_install
  env_copy "${RELEASE_STAGE}"
  # shellcheck disable=SC2154
  log_start "SYNCING FILES TO REMOTE BUILD FOLDER"
  for ip in "${ips[@]}";
    do
      rsync_to_web_server ${ip} ubuntu;
    done
  log_end "SYNCING FILES TO REMOTE BUILD FOLDER"
  env_copy "${RELEASE_STAGE}-remote"
  db_migrate
  log_start "SYNCING FILES TO RELEASE FOLDER FROM BUILD FOLDER"
  release_folder=$(deploy_release_folder)
  for ip in "${ips[@]}"; do
    ssh root@${ip} 'bash -s' ${release_folder} <${QM_API}/scripts/deploy/sync_to_release_folder.sh;
  done
  synchronize_server_time
}
function synchronize_server_time() {
  output_disable
  d=$(date +"%T") && log_info "Server time BEFORE sync: ${d}"
  sudo date -s "$(wget -qSO- --max-redirect=0 google.com 2>&1 | grep Date: | cut -d' ' -f5-8)Z"
  tz=$(cat /etc/timezone)
  d=$(date +"%T")
  log_info "Server time AFTER sync: ${d} in timezone $tz"
}
function get_my_ip() {
    local _ip _line
    while IFS=$': \t' read -a _line ;do
        [ -z "${_line%inet}" ] &&
           _ip=${_line[${#_line[1]}>4?1:2]} &&
           [ "${_ip#127.0.0.1}" ] && echo $_ip && return 0
      done< <(LANG=C /sbin/ifconfig)
}
function github_global_config_options() {
  #git config --global core.autocrlf false
  git config --global core.eol lf
  output_disable
  log_info "git config --global github.token [HIDDEN]"
  git config --global github.token "$(github_access_token)"
}
function git_config(){
  log_start_of_function
  github_global_config_options
  git config --global user.email "m@quantimodo.com"
  git config --global user.name "mikepsinn"
  #sudo git config --global core.autocrlf false
  log_end_of_function
}
function etckeeper_setup() {
  install_unattended etckeeper
  local host_distro="$(etckeeper_branch)"
  output_enable
  sudo git config --global user.email "root@$host_distro"
  sudo git config --global user.name "root $host_distro"
  sudo git config --global core.autocrlf false
  sudo git config --global core.eol lf
  output_disable
  log_info "git config --global github.token [HIDDEN]"
  sudo git config --global github.token "$(github_access_token)"
  sudo cp -r $REPO_CONFIGS_PATH/etc-global/.git* /etc/
  sudo cp -r $REPO_CONFIGS_PATH/etc-global/etckeeper/* /etc/etckeeper/
}
function log_environment(){
  log_start_of_function
  printenv >&2
  log_end_of_function
}
function etckeeper_branch(){
  local branch="$(hostname)"
  if [ -z ${WSL_DISTRO_NAME:-} ]; then
      log_info "WSL_DISTRO_NAME is empty so falling back to Ubuntu-20.04"
      WSL_DISTRO_NAME="Ubuntu-20.04"
      log_environment
  else
      log_info "WSL_DISTRO_NAME is $WSL_DISTRO_NAME"
      branch=$branch-$WSL_DISTRO_NAME
  fi
  local release="$(lsb_release -r)"
  if [ -z "${release:-}" ]; then
    log_info "lsb_release -r is empty"
  else
    log_info "lsb_release -r is: $release"
    # It's impossible to remove the space for some reason branch=$branch-$release
  fi
  branch="${branch// /-}"
  branch="${branch//:/-}"
  log_info "etckeeper branch is $branch"
  echo "$branch"
}
function etckeeper_push() {
  log_start_of_function
  local msg="$script_name: ${1:-}"
  local branch="$(etckeeper_branch)"
  log_info "https://wiki.archlinux.org/title/Etckeeper#Automatic_push_to_remote_repo"
  cd /etc
  sudo git remote add origin https://$(github_access_token)@github.com/mikepsinn/etc-keeper.git || true
  output_enable
  sudo git add .
  sudo git commit -m "$msg" || true
  sudo git push -u origin HEAD:$branch -f
  output_disable
  log_end_of_function
}
function install_if_necessary2() {
	local REQUIRED_PKG=$(validate_param "${1:-}" "REQUIRED_PKG" 1)
  PKG_OK=$(dpkg-query -W --showformat='${Status}\n' $REQUIRED_PKG|grep "install ok installed")
  log_info "Checking for $REQUIRED_PKG: $PKG_OK"
  if [ "" = "$PKG_OK" ]; then
      log_info "No $REQUIRED_PKG. Setting up $REQUIRED_PKG."
      install_unattended $REQUIRED_PKG
    else
      log_info "$REQUIRED_PKG already installed."
  fi
}
function install_if_necessary() {
	local REQUIRED_PKG=$(validate_param "${1:-}" "REQUIRED_PKG" 1)
  if ! dpkg -s $REQUIRED_PKG >/dev/null 2>&1; then
    install_unattended $REQUIRED_PKG
  fi
}

function install_mongodb() {
    output_enable
    apt-key adv --keyserver hkp://keyserver.ubuntu.com:80 --recv 0C49F3730359A14518585931BC711F9BA15703C6
    echo "deb http://repo.mongodb.org/apt/ubuntu trusty/mongodb-org/3.4 multiverse" | sudo tee /etc/apt/sources.list.d/mongodb-org-3.4.list
    log_info "Have to apt-get update after adding to /etc/apt/sources.list"
    apt_update
    apt-get -y install mongodb-org re2c
    sudo pecl install mongodb
    ln -s /usr/lib/php/20151012/mongodb.so /usr/lib/php/20170718/mongodb.so
    ln -s /usr/lib/php/20160303/mongodb.so /usr/lib/php/20170718/mongodb.so
    phpenmod mongodb
    # auto-remove records older than 2592000 seconds (30 days)
    mongo xhprof --eval 'db.collection.ensureIndex( { "meta.request_ts" : 1 }, { expireAfterSeconds : 2592000 } )'
    # indexes
    mongo xhprof --eval  "db.collection.ensureIndex( { 'meta.SERVER.REQUEST_TIME' : -1 } )"
    mongo xhprof --eval  "db.collection.ensureIndex( { 'profile.main().wt' : -1 } )"
    mongo xhprof --eval  "db.collection.ensureIndex( { 'profile.main().mu' : -1 } )"
    mongo xhprof --eval  "db.collection.ensureIndex( { 'profile.main().cpu' : -1 } )"
    mongo xhprof --eval  "db.collection.ensureIndex( { 'meta.url' : 1 } )"
    update-rc.d mongodb defaults
    update-rc.d mongodb enable
}
function xhgui_install(){
  xhgui_folder=$QM_API/configs/xhgui
  log_start_of_function
  log_info "Installing Tideways & XHGui"
  phpenmod tideways_xhprof
  #composer_install
  cd "${QM_API}"/vendor/perftools/xhgui
  php install.php
  output_enable
  cp -R ${xhgui_folder}/overrides/* ${QM_API}/vendor/perftools/xhgui/
  log_info "Removing /etc/php/7.4/mods-available/xhgui.ini external_header.php include because we have our own triggering mechanism..."
  sudo rm /etc/php/7.4/mods-available/xhgui.ini || true
  log_info "* Added xhgui.vvv.test to /etc/hosts"
  echo "127.0.0.1 xhgui.vvv.test # vvv-provision" | sudo tee -a /etc/hosts
  restart_web_services
  log_end_of_function
}
function tideways_install(){
	# What was this for? sudo rm "/etc/php/7.4/mods-available/tideways_xhprof.ini";
	install_unattended php-tideways
  sudo phpenmod -v ALL tideways
}
function dot_files(){
  sh -c "$(curl -fsLS git.io/chezmoi)"
  ./bin/chezmoi init
}
function fix_tmp_folder(){
  log_info "Fixing /tmp folder permissions..."
  sudo mkdir /tmp || true
  sudo chmod 1777 /tmp || true
}
function log_lightsail_links(){
  if [ -z "${NODE_NAME:-}" ]; then
      log_debug 'NODE_NAME is empty'
  else
    log_divider
    log_message "SSH Access: https://lightsail.aws.amazon.com/ls/remote/us-east-1/instances/$NODE_NAME/terminal?protocol=ssh"
    log_message "Delete if troublesome at https://lightsail.aws.amazon.com/ls/webapp/us-east-1/instances/$NODE_NAME/delete"
    log_message "and recreate by running http://quantimodo2.asuscomm.com:8082/view/Deploy/job/buy-phpunit-slaves/"
    log_divider
  fi
}
function provision(){
  cd $QM_API
  scripts=(
    scripts/install-xhgui.sh
  )
  for script in "${scripts[@]}" ; do
      sudo bash $script
  done
  install_filebeat_for_logz_io
}
function install_nvm(){
  local username=$(validate_param "${1:-}" "username" 1)
  if [ -f "/home/$username/.nvm" ]; then
      log_info "nvm already installed."
  else
    install_if_necessary curl
    log_info "Installing nvm..."
    cd /home/$username
    sudo chown -R $username:$(id -gn $username) /home/$username/.config
    sudo curl https://raw.githubusercontent.com/creationix/nvm/master/install.sh | bash
  fi
  # don't do this here nvm_load
}
function nvm_load(){
    log_info "Loading nvm command for shell access..."
    output_disable
    # shellcheck disable=SC2155
    export NVM_DIR="$([ -z "${XDG_CONFIG_HOME-}" ] && printf %s "${HOME}/.nvm" || printf %s "${XDG_CONFIG_HOME}/nvm")"
    [ -s "$NVM_DIR/nvm.sh" ] && \. "$NVM_DIR/nvm.sh" # This loads nvm
}
function install_filebeat_for_logz_io(){
  apt_key_add https://artifacts.elastic.co/GPG-KEY-elasticsearch
  install_if_necessary apt-transport-https
  echo "deb https://artifacts.elastic.co/packages/7.x/apt stable main" | sudo tee -a /etc/apt/sources.list.d/elastic-7.x.list
  log_info "Have to apt-get update after adding to /etc/apt/sources.list"
  apt_update
  install_unattended filebeat
  sudo systemctl enable filebeat
  sudo curl https://raw.githubusercontent.com/logzio/public-certificates/master/AAACertificateServices.crt --create-dirs -o /etc/pki/tls/certs/COMODORSADomainValidationSecureServerCA.crt
  sudo cp $QM_API/configs/etc-global/filebeat/filebeat.yml /etc/filebeat/filebeat.yml
  sudo service filebeat start
}
function install_phantomjs(){
  phantom_version="phantomjs-1.9.8"
  arch=$(uname -m)
  if ! [ $arch = "x86_64" ]; then
    arch="i686"
  fi
  phantom_js="$phantom_version-linux-$arch"
  apt_update_if_necessary
  sudo apt-get install build-essential chrpath libssl-dev libxft-dev -y >/dev/null
  sudo apt-get install libfreetype6 libfreetype6-dev -y >/dev/null
  sudo apt-get install libfontconfig1 libfontconfig1-dev -y >/dev/null
  cd ~
  wget https://bitbucket.org/ariya/phantomjs/downloads/$phantom_js.tar.bz2
  sudo tar xvjf $phantom_js.tar.bz2
  sudo mv $phantom_js /usr/local/share
  sudo ln -sf /usr/local/share/$phantom_js/bin/phantomjs /usr/local/bin
}
function install_ssh_wsl() {
  etckeeper_push "Before install_ssh_wsl"
  sudo apt purge -y openssh-server
  sudo apt install openssh-server
  sudo cp -R $QM_API/configs/etc-dev/ssh/* /etc/ssh/
  sudo service ssh --full-restart
  etckeeper_push "After install_ssh_wsl"
}
function install_global_packages(){
  packages=(
    curl
    git
    htop
    libfcgi-dev
    lynx
    mc
    memcached
    ncdu
    nginx
    openjdk-8-jre
    openssh-server
    pandoc
    percona-toolkit
    s3cmd
    software-properties-common
    unzip
    vim
    zip
  )
  for package in "${packages[@]}" ; do
      install_if_necessary "$package"
  done
  install_nvm $WSL_USER_NAME
  #install_filebeat_for_logz_io
  install_phantomjs
  #jenkins_install
  # Not compatible with 20.04 yet install_nginx_amplify
  etc_copy_and_restart
}
function disable_password_requirement(){
	local username=$(validate_param "${1:-}" "username" 1)
  log_info "Give $username user passwordless sudo..."
cat >> /etc/sudoers <<EOF
$username ALL=(ALL) NOPASSWD: ALL
EOF
}
function create_user() {
	local username=$(validate_param "${1:-}" "username" 1)
	local pw=$(validate_param "${2:-}" "password" 2)
  log_info "Creating $username user with password of $pw..."
  useradd $username
  echo -e "$username\n$username" | passwd $pw
  ssh_setup $username
}
function set_root_password() {
	local pwd=$(validate_param "${1:-}" "root password " 1)
  log_info "Setting root password to $pwd..."
  echo -e "$pwd\n$pwd" | sudo i passwd
}
function git_clone_if_necessary() {
  new_line
	local repo_url=$(validate_param "${1:-}" "repo_url" 1)
	local dest=$(validate_param "${2:-}" "dest dir" 2)
	if ! folder_exists "$dest" ; then
    git clone "$repo_url" "$dest"
  fi
  new_line
}
function disable_zsh_git_prompt() {
	git config oh-my-zsh.hide-info 1
}
function imagemagick_install() {
  log_start_of_function
  log_info "https://stackoverflow.com/questions/58623596/failed-to-get-imagick-load-for-php7-4"
  log_info "https://github.com/laravel/homestead/issues/1312"
  apt_update
  output_enable
  install_unattended libmagickwand-dev
  install_unattended imagemagick
  install_unattended php${PHP_VERSION}-imagick
  php -m | grep imagick
  php-config --extension-dir
  ext_dir=$(php-config --extension-dir)
  output_disable
  local file=$ext_dir/imagick.so
  if [ -f "$file" ]; then
      log_info "$file exists so imagick appears to have installed"
  else
      log_info "$file does not exist so imagick probably failed"
  fi
	#sudo cp /usr/lib/php/20200930/imagick.so /usr/lib/php/20190902/imagick.so || true
  log_info "make sure extension=imagick is in php.ini. imagemagick is stupid."
  log_end_of_function
}
function setup_swap() {
	local SWAP_SIZE=$(validate_param "${1:-}" "SWAP_SIZE i.e. 2G" 1)
log_info "=========================================================="
log_info "Welcome to CraftThatBlock's Ubuntu Swap install script!"
log_info "This script will automatically setup a swap file,"
log_info "install it, and do everything else needed."
log_info "All you have to do is enter your password and hit enter!"
log_info "=========================================================="
SWAP_PATH=${2:-"/swapfile"}
sudo fallocate -l ${SWAP_SIZE} ${SWAP_PATH}
sudo chmod 600 ${SWAP_PATH}
sudo mkswap ${SWAP_PATH}
sudo swapon ${SWAP_PATH}
echo "$SWAP_PATH   none    swap    sw    0   0" | sudo tee /etc/fstab -a
sudo sysctl vm.swappiness=10
echo "vm.swappiness=10" | sudo tee /etc/sysctl.conf -a
sudo sysctl vm.vfs_cache_pressure=50
echo "vm.vfs_cache_pressure=50" | sudo tee /etc/sysctl.conf -a
log_info "=========================================================="
log_info "Done! To apply these changes you simply have to restart:"
log_info "sudo reboot now"
log_info "=========================================================="
}
function delete_env_files() {
  log_info "Deleting env files..."
  find . -name '*.env' -delete
}
function fix_line_endings() {
	local path_to_repo=$(validate_param "${1:-}" "relative path to repo you want to fix" 1)
	go_to_repo_root
	cd $path_to_repo
    #####################
  # From https://gist.github.com/ajdruff/16427061a41ca8c08c05992a6c74f59e
  # Use this with or without the .gitattributes snippet with this Gist
  # Why do you want this ? Because Git will see diffs between files shared between Linux and Windows due to differences in line ending handling ( Windows uses CRLF and Unix LF)
  # This Gist normalizes handling by forcing everything to use Unix style.
  #####################
  # Fix Line Endings - Force All Line Endings to LF and Not Windows Default CR or CRLF
  # Taken largely from: https://help.github.com/articles/dealing-with-line-endings/
  # With the exception that we are forcing LF instead of converting to windows style.
  log_info "Set LF as your line ending default."
  git config --global core.eol lf
  log_info "Set autocrlf to false to stop converting between windows style (CRLF) and Unix style (LF)"
  git config --global core.autocrlf false
  log_info "Save your current files in Git, so that none of your work is lost."
  git add . -u
  git commit -m "Saving files before refreshing line endings"
  log_info "Remove the index and force Git to rescan the working directory."
  rm .git/index
  log_info "Rewrite the Git index to pick up all the new line endings."
  git reset
  log_info "Show the rewritten, normalized files."
  git status
  log_info "Add all your changed files back, and prepare them for a commit. This is your chance to inspect which files, if any, were unchanged."
  git add -u
  log_info "It is perfectly safe to see a lot of messages here that read"
  log_info "warning: CRLF will be replaced by LF in file."
  log_info "Rewrite the .gitattributes file."
  git add .gitattributes
  log_info "Commit the changes to your repository."
  git commit -m "Normalize all the line endings"
}
function copy_folder_over_another() {
  new_line
	local src=$(validate_param "${1:-}" "source folder" 1)
	local dst=$(validate_param "${2:-}" "destination folder" 2)
	log_info "Merging $src into $dst..."
  cp -Rf $src/* $dst/
  log_info "Done merging $src into $dst"
  new_line
}
function go_access(){
  local path=$(validate_param "${1:-}" "path to access.log" 1)
  cd "$QM_API"
  url=https://local.quantimo.do/goaccess.html
  xdg-open $url
  log_message "Go to:
  $url
  "
  sudo goaccess $path -o public/goaccess.html --log-format=COMBINED --real-time-html
}
function link_to_logs_folder(){
  local source_path=$(validate_param "${1:-}" "path to link" 1)
  local link=$(validate_param "${2:-$(path_to_file_name $source_path)}" "link name" 2)
  link_source_to_link $source_path "$LOGS_FOLDER/$link"
}
function link_to_links_folder(){
  local path=$(validate_param "${1:-}" "path to log" 1)
  local link=$(validate_param "${2:-$(path_to_file_name $path)}" "link" 2)
  link_source_to_link $path "$LINKS_FOLDER/$link"
}
function move_to_dropbox_and_link(){
  no_root
  local path=$(validate_param "${1:-}" "path to source" 1)
  move_and_replace_with_link $path "$DROPBOX_FOLDER$path"
}
