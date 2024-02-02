#!/usr/bin/env bash
#
# Contributors: ADD YOUR NAME HERE, Mike P. Sinn | License: Open Source Under GNU General Public License v3.0
#

# shellcheck disable=SC2046
# shellcheck source=./all_functions.sh
source "$( cd "$(dirname "${BASH_SOURCE[0]}")" ; pwd -P )/all_functions.sh" "${BASH_SOURCE[0]}"
output_enable


CATEGORY_NAME=economics

source_dotenv

#######################################
# description
# Globals:
#   S3KEY
#   S3SECRET
#   acl
#   aws_path
#   bucket
#   content_type
#   date
#   file
#   path
#   signature
#   string
# Arguments:
#   1
#   2
#   3
#######################################
function putS3() {
  path=$1
  file=$2
  aws_path=$3
  bucket='qmimages'
  date=$(date +"%a, %d %b %Y %T %z")
  acl="x-amz-acl:public-read"
  content_type='application/x-compressed-tar'
  string="PUT\n\n$content_type\n$date\n$acl\n/$bucket$aws_path$file"
  signature=$(echo -en "${string}" | openssl sha1 -hmac "${S3SECRET}" -binary | base64)
  curl -X PUT -T "$path/$file" \
    -H "Host: $bucket.s3.amazonaws.com" \
    -H "Date: $date" \
    -H "Content-Type: $content_type" \
    -H "$acl" \
    -H "Authorization: AWS ${S3KEY}:$signature" \
    "https://$bucket.s3.amazonaws.com$aws_path$file"
}

BASE_GENERATED_IMAGE_PATH=${QM_API}/tmp/quantimodo-design
mkdir ${BASE_GENERATED_IMAGE_PATH} || true

GAUGE_WITH_CAT_PATH=${BASE_GENERATED_IMAGE_PATH}/variable_categories_gauges
mkdir ${GAUGE_WITH_CAT_PATH} || true
rm -rf ${GAUGE_WITH_CAT_PATH}/*

GAUGE_WITH_CAT_BACKGROUND_PATH=${BASE_GENERATED_IMAGE_PATH}/variable_categories_gauges_background
mkdir ${GAUGE_WITH_CAT_BACKGROUND_PATH} || true
rm -rf ${GAUGE_WITH_CAT_BACKGROUND_PATH}/*

GAUGE_WITH_CAT_LOGO_PATH=${BASE_GENERATED_IMAGE_PATH}/variable_categories_gauges_logo
mkdir ${GAUGE_WITH_CAT_LOGO_PATH} || true
rm -rf ${GAUGE_WITH_CAT_LOGO_PATH}/*

GAUGE_WITH_CAT_LOGO_BACKGROUND_PATH=${BASE_GENERATED_IMAGE_PATH}/variable_categories_gauges_logo_background
mkdir ${GAUGE_WITH_CAT_LOGO_BACKGROUND_PATH} || true
rm -rf ${GAUGE_WITH_CAT_LOGO_BACKGROUND_PATH}/*

COMBINED_CATEGORIES_PATH=${BASE_GENERATED_IMAGE_PATH}/variable_categories_combined
mkdir ${COMBINED_CATEGORIES_PATH} || true
rm -rf ${COMBINED_CATEGORIES_PATH}/*

#QM_LOGO_FILE_PATH=${IONIC_PATH}/src/img/logos/quantimodo-colorful-logo-path-820-168.png
#QM_LOGO_FILE_PATH=${IONIC_PATH}/src/img/logos/quantimodo-colorful-logo-path-400-82.png
#QM_LOGO_FILE_PATH=${IONIC_PATH}/src/img/logos/quantimodo-colorful-logo-path-600-120.png
QM_LOGO_FILE_PATH=${IONIC_PATH}/src/img/logos/quantimodo-logo-simple-110-280.png
QM_LOGO_FILE_PATH=${IONIC_PATH}/src/img/logos/quantimodo-logo-simple-480-290.png
QM_LOGO_FILE_PATH=${IONIC_PATH}/src/img/logos/quantimodo-logo-simple-300-60.png
QM_LOGO_FILE_PATH=${IONIC_PATH}/src/img/logos/quantimodo-logo-white-310x66.png

BACKGROUND_FILE_PATH=${IONIC_PATH}/src/img/backgrounds/bokeh-on-blue-1200-630.jpg
BACKGROUND_FILE_PATH=${IONIC_PATH}/src/img/backgrounds/purple-space-stars-background-1200-630.jpg

COMBINED_CATEGORIES_LOGO_PATH=${BASE_GENERATED_IMAGE_PATH}/variable_categories_combined_logo
mkdir ${COMBINED_CATEGORIES_LOGO_PATH} || true
rm -rf ${COMBINED_CATEGORIES_LOGO_PATH}/*

ROBOT_INPUTS_PATH=${IONIC_PATH}/src/img/robots
ROBOT_OUTPUTS_PATH=${BASE_GENERATED_IMAGE_PATH}/robots
ROBOT_PNG_FILE_PATH=${ROBOT_OUTPUTS_PATH}/quantimodo-robot-puzzled-white.png
#ROBOT_PNG_FILE_PATH=${IONIC_PATH}/src/img/robots/quantimodo-robot-puzzled-white-213-300.png

mkdir ${ROBOT_OUTPUTS_PATH} || true
rm -rf ${ROBOT_OUTPUTS_PATH}/*
cd ${ROBOT_INPUTS_PATH}
mogrify -path ${ROBOT_OUTPUTS_PATH} -format png -background transparent -gravity center -scale 320x450 -extent 400x630 *.svg

COMBINED_CATEGORIES_ROBOT_PATH=${BASE_GENERATED_IMAGE_PATH}/variable_categories_combined_robot
mkdir ${COMBINED_CATEGORIES_ROBOT_PATH} || true
rm -rf ${COMBINED_CATEGORIES_ROBOT_PATH}/*

COMBINED_CATEGORIES_LOGO_ROBOT_PATH=${BASE_GENERATED_IMAGE_PATH}/variable_categories_combined_logo_robot
mkdir ${COMBINED_CATEGORIES_LOGO_ROBOT_PATH} || true
rm -rf ${COMBINED_CATEGORIES_LOGO_ROBOT_PATH}/*

COMBINED_CATEGORIES_SMALL_LOGO_ROBOT_PATH=${BASE_GENERATED_IMAGE_PATH}/variable_categories_combined_small_logo_robot
mkdir ${COMBINED_CATEGORIES_SMALL_LOGO_ROBOT_PATH} || true
rm -rf ${COMBINED_CATEGORIES_SMALL_LOGO_ROBOT_PATH}/*

COMBINED_CATEGORIES_SMALL_LOGO_ROBOT_BACKGROUND_PATH=${BASE_GENERATED_IMAGE_PATH}/variable_categories_combined_small_logo_robot_background
mkdir ${COMBINED_CATEGORIES_SMALL_LOGO_ROBOT_BACKGROUND_PATH} || true
rm -rf ${COMBINED_CATEGORIES_SMALL_LOGO_ROBOT_BACKGROUND_PATH}/*

COMBINED_CATEGORIES_ROBOT_BACKGROUND_PATH=${BASE_GENERATED_IMAGE_PATH}/variable_categories_combined_robot_background
mkdir ${COMBINED_CATEGORIES_ROBOT_BACKGROUND_PATH} || true
rm -rf ${COMBINED_CATEGORIES_ROBOT_BACKGROUND_PATH}/*

VARIABLE_CATEGORY_PNG_OUTPUTS_PATH=${BASE_GENERATED_IMAGE_PATH}/variable_categories_pngs
mkdir ${VARIABLE_CATEGORY_PNG_OUTPUTS_PATH} || true
rm -rf ${VARIABLE_CATEGORY_PNG_OUTPUTS_PATH}/*
VARIABLE_CATEGORY_PNG_OUTPUTS=${VARIABLE_CATEGORY_PNG_OUTPUTS_PATH}/*
VARIABLE_CATEGORY_SVG_INPUTS_PATH=${IONIC_PATH}/src/img/variable_categories
VARIABLE_CATEGORY_SVG_INPUTS=${VARIABLE_CATEGORY_SVG_INPUTS_PATH}/*
cd ${VARIABLE_CATEGORY_SVG_INPUTS_PATH}
#mogrify -path ${PNG_OUTPUTS_PATH} -format png -background transparent -gravity center -scale 300x300 -extent 600x630 *.svg
mogrify -path ${VARIABLE_CATEGORY_PNG_OUTPUTS_PATH} -format png -background transparent -gravity center -scale 300x300 -extent 400x630 *.svg

GAUGE_INPUTS_PATH=${IONIC_PATH}/src/img/gauges/246-120
GAUGE_INPUTS=${GAUGE_INPUTS_PATH}/*
GAUGE_OUTPUTS_PATH=${BASE_GENERATED_IMAGE_PATH}/gauges
cd ${GAUGE_INPUTS_PATH}
mkdir ${GAUGE_OUTPUTS_PATH} || true
rm -rf ${GAUGE_OUTPUTS_PATH}/*
GAUGE_OUTPUTS=${GAUGE_OUTPUTS_PATH}/*
cd ${GAUGE_INPUTS_PATH}
mogrify -path ${GAUGE_OUTPUTS_PATH} -format png -background transparent -gravity center -scale 369x180 -extent 400x630 *.png

for cause_variable_category_png in ${VARIABLE_CATEGORY_PNG_OUTPUTS}; do
  echo "Processing $cause_variable_category_png file..."
  # take action on each file. $f store current file name
  for effect_variable_category_png in ${VARIABLE_CATEGORY_PNG_OUTPUTS}; do
    if [[ $effect_variable_category_png =~ $CATEGORY_NAME || $cause_variable_category_png =~ $CATEGORY_NAME ]]; then
           echo "
Generating for $cause_variable_category_png and $effect_variable_category_png
"
    else
            echo "
Skipping $effect_variable_category_png $cause_variable_category_png
"
            continue
    fi
    cause_variable_category_png_filename=$(basename "$cause_variable_category_png")
    cause_variable_category_name="${cause_variable_category_png_filename%.*}"
    effect_variable_category_png_filename=$(basename "$effect_variable_category_png")
    effect_variable_category_name="${effect_variable_category_png_filename%.*}"
    combined_category_file_path=${COMBINED_CATEGORIES_PATH}/${cause_variable_category_name}_${effect_variable_category_name}.png
    echo "Combining $cause_variable_category_png and $effect_variable_category_png to $combined_category_file_path..."
    convert +append ${cause_variable_category_png} ${effect_variable_category_png} ${combined_category_file_path}
    for gauge_file_path in ${GAUGE_OUTPUTS}; do

        gauge_filename=$(basename "$gauge_file_path")
        gauge_name="${gauge_filename%.*}"
        gauge_category_file_path=${GAUGE_WITH_CAT_PATH}/${gauge_name}_${cause_variable_category_name}_${effect_variable_category_name}.png

        convert +append ${cause_variable_category_png} ${gauge_file_path} ${effect_variable_category_png} ${gauge_category_file_path}

        gauge_combined_category_logo_file_path=${GAUGE_WITH_CAT_LOGO_PATH}/${gauge_name}_${cause_variable_category_name}_${effect_variable_category_name}_logo.png
        convert ${gauge_category_file_path} ${QM_LOGO_FILE_PATH} -gravity southeast -composite -format png -quality 90 ${gauge_combined_category_logo_file_path}

        gauge_combined_category_logo_background_file_path=${GAUGE_WITH_CAT_LOGO_BACKGROUND_PATH}/${gauge_name}_${cause_variable_category_name}_${effect_variable_category_name}_logo_background.png
        convert ${BACKGROUND_FILE_PATH} ${gauge_combined_category_logo_file_path} -composite -format png -quality 90 ${gauge_combined_category_logo_background_file_path}

        gauge_combined_category_background_file_path=${GAUGE_WITH_CAT_BACKGROUND_PATH}/${gauge_name}_${cause_variable_category_name}_${effect_variable_category_name}_background.png
        convert ${BACKGROUND_FILE_PATH} ${gauge_category_file_path} -composite -format png -quality 90 ${gauge_combined_category_background_file_path}

        combined_category_logo_file_path=${COMBINED_CATEGORIES_LOGO_PATH}/${cause_variable_category_name}_${effect_variable_category_name}_logo.png
        convert ${combined_category_file_path} ${QM_LOGO_FILE_PATH} -gravity southeast -composite -format png -quality 90 ${combined_category_logo_file_path}

        combined_category_robot_file_path=${COMBINED_CATEGORIES_ROBOT_PATH}/${cause_variable_category_name}_${effect_variable_category_name}_robot.png
        convert +append ${cause_variable_category_png} ${ROBOT_PNG_FILE_PATH} ${effect_variable_category_png} ${combined_category_robot_file_path}

        combined_category_logo_robot_file_path=${COMBINED_CATEGORIES_LOGO_ROBOT_PATH}/${cause_variable_category_name}_${effect_variable_category_name}_logo_robot.png
        convert ${combined_category_robot_file_path} ${QM_LOGO_FILE_PATH} -gravity south -composite -format png -quality 90 ${combined_category_logo_robot_file_path}

        combined_category_robot_background_file_path=${COMBINED_CATEGORIES_ROBOT_BACKGROUND_PATH}/${cause_variable_category_name}_${effect_variable_category_name}_robot_background.png
        convert ${BACKGROUND_FILE_PATH} ${combined_category_robot_file_path} -composite -format png -quality 90 ${combined_category_robot_background_file_path}

        combined_category_small_logo_robot_file_path=${COMBINED_CATEGORIES_SMALL_LOGO_ROBOT_PATH}/${cause_variable_category_name}_${effect_variable_category_name}_small_logo_robot.png
        convert ${combined_category_robot_file_path} ${QM_LOGO_FILE_PATH} -gravity southeast -composite -format png -quality 90 ${combined_category_small_logo_robot_file_path}

        combined_category_small_logo_robot_background_file_path=${COMBINED_CATEGORIES_SMALL_LOGO_ROBOT_BACKGROUND_PATH}/${cause_variable_category_name}_${effect_variable_category_name}_small_logo_robot_background.png
        convert ${BACKGROUND_FILE_PATH} ${combined_category_small_logo_robot_file_path} -composite -format png -quality 90 ${combined_category_small_logo_robot_background_file_path}
    done
  done
done

path=${GAUGE_WITH_CAT_LOGO_PATH}
for file in "$path"/*; do
  //echo "Uploading " "${file##*/}"
  #putS3 "$path" "${file##*/}" "/"
done

log_end_of_script
