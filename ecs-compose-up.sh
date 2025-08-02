ecs-cli compose --project-name sistemtoko  --file ecs-compose.yml \
--debug service up  \
--deployment-max-percent 100 --deployment-min-healthy-percent 0 \
--region ap-southeast-1 --ecs-profile andy --cluster-config sistemtoko