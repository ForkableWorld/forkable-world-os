create table quantimodo_test.lightsail_instances
(
    id                  int unsigned auto_increment comment 'Automatically generated unique id for the lightsail instance'
        primary key,
    client_id           varchar(80) charset utf8mb3         null comment 'The ID for the API client that created the record',
    created_at          timestamp default CURRENT_TIMESTAMP not null comment 'The time the record was originally created',
    deleted_at          timestamp                           null comment 'The time the record was deleted',
    updated_at          timestamp default CURRENT_TIMESTAMP not null on update CURRENT_TIMESTAMP comment 'The time the record was last modified',
    user_id             bigint unsigned                     not null comment 'The user ID for the owner of the record',
    name                varchar(255)                        not null comment 'Example: cc-wp',
    arn                 varchar(255)                        not null comment 'Example: arn:aws:lightsail:us-east-1:335072289018:Instance/14eb6cec-1c74-429a-96f5-8f8f5e5fbbc1',
    support_code        varchar(255)                        not null comment 'Example: 102336889266/i-005d61af88d99927e',
    external_created_at varchar(255)                        not null comment 'Example: 2021-03-22T01:47:10+00:00',
    location            longtext                            not null comment 'Example: {availabilityZone:us-east-1a,regionName:us-east-1}',
    resource_type       varchar(255)                        not null comment 'Example: Instance',
    tags                longtext                            not null comment 'Example: [{key:wordpress},{key:HEALTH_CHECK_URL,value:https://CrowdsourcingCures.org}]',
    blueprint_id        varchar(255)                        not null comment 'Example: wordpress',
    blueprint_name      varchar(255)                        not null comment 'Example: WordPress',
    bundle_id           varchar(255)                        not null comment 'Example: micro_2_0',
    add_ons             longtext                            not null comment 'Example: [{name:AutoSnapshot,status:Enabled,snapshotTimeOfDay:00:00}]',
    is_static_ip        tinyint(1)                          not null comment 'Example: 1',
    private_ip_address  varchar(255)                        not null comment 'Example: 172.26.7.226',
    public_ip_address   varchar(255)                        not null comment 'Example: 3.224.6.200',
    ipv6_addresses      longtext                            not null comment 'Example: [2600:1f18:1ae:1700:c92e:b66f:52cd:5f01]',
    ip_address_type     varchar(255)                        not null comment 'Example: dualstack',
    hardware            longtext                            not null comment 'Example: {cpuCount:1,disks:[{createdAt:2021-03-22T01:47:10+00:00,sizeInGb:40,isSystemDisk:true,iops:120,path:/dev/xvda,attachedTo:cc-wp,attachmentState:attached}],ramSizeInGb:1}',
    networking          longtext                            not null comment 'Example: {monthlyTransfer:{gbPerMonthAllocated:2048},ports:[{fromPort:80,toPort:80,protocol:tcp,accessFrom:Anywhere (0.0.0.0/0),accessType:public,commonName:,accessDirection:inbound,cidrs:[0.0.0.0/0],ipv6Cidrs:[],cidrListAliases:[]},{fromPort:8000,toPort:8000,protocol:tcp,accessFrom:Custom,accessType:public,commonName:,accessDirection:inbound,cidrs:[24.178.226.180/32],ipv6Cidrs:[],cidrListAliases:[]},{fromPort:7777,toPort:7777,protocol:tcp,accessFrom:Custom,accessType:public,commonName:,accessDirection:inbound,cidrs:[24.178.226.180/32],ipv6Cidrs:[],cidrListAliases:[]},{fromPort:6379,toPort:6379,protocol:tcp,accessFrom:Custom,accessType:public,commonName:,accessDirection:inbound,cidrs:[24.178.226.180/32],ipv6Cidrs:[],cidrListAliases:[]},{fromPort:888,toPort:888,protocol:tcp,accessFrom:Custom,accessType:public,commonName:,accessDirection:inbound,cidrs:[24.178.226.180/32],ipv6Cidrs:[],cidrListAliases:[]},{fromPort:20,toPort:20,protocol:tcp,accessFrom:Custom,accessType:public,commonName:,accessDirection:inbound,c',
    state               longtext                            not null comment 'Example: {code:16,name:running}',
    username            varchar(255)                        not null comment 'Example: bitnami',
    ssh_key_name        varchar(255)                        not null comment 'Example: qm-aws-20160528',
    jenkins_labels      longtext                            not null comment 'Example: []',
    computer            varchar(255)                        not null comment 'Example: ',
    constraint lightsail_instances_client_id_fk
        foreign key (client_id) references quantimodo_test.oa_clients (client_id),
    constraint lightsail_instances_wp_users_ID_fk
        foreign key (user_id) references quantimodo_test.wp_users (ID)
)
    collate = utf8mb4_bin;

