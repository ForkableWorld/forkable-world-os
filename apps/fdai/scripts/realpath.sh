#
# Contributors: ADD YOUR NAME HERE, Mike P. Sinn | License: Open Source Under GNU General Public License v3.0
#

realpath ()
{
    f=$@;
    if [ -z "$f" ]; then
      f=$(pwd)
    fi
    if [ -d "$f" ]; then
        base="";
        dir="$f";
    else
        base="/$(basename "$f")";
        dir=$(dirname "$f");
    fi;
    dir=$(cd "$dir" && /bin/pwd -P);
    echo "$dir$base"
}
