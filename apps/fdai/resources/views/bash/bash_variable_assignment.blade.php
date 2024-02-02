# FOLDER_VARIABLES_GO_HERE
#!/bin/bash
cd "$(dirname "${BASH_SOURCE[0]}")"
# shellcheck source=./all_functions.sh
source "$(pwd -P)/all_functions.sh" "${BASH_SOURCE[0]}"
echo "See https://etckeeper.branchable.com/README/ for instructions"
etckeeper_push
log_end_of_script@endverbatim
cd "${this_script_folder}" || exit 1 && cd {{$file->getDotsPathToRoot()}} && export QM_API="$PWD"
# FOLDER_VARIABLES_END_HERE
