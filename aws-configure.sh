#!/bin/bash
set -e
CLUSTER_NAME=sistemtoko
REGION=ap-southeast-1
LAUNCH_TYPE=EC2
PROFILE_NAME=andy
ecs-cli configure --cluster "$CLUSTER_NAME" --default-launch-type "$LAUNCH_TYPE" --region "$REGION" --config-name "$PROFILE_NAME"