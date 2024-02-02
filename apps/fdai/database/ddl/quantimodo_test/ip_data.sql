create table quantimodo_test.ip_data
(
    id             int unsigned auto_increment comment 'Automatically generated unique id for the ip data'
        primary key,
    created_at     timestamp default CURRENT_TIMESTAMP not null comment 'The time the record was originally created',
    deleted_at     timestamp                           null comment 'The time the record was deleted',
    updated_at     timestamp default CURRENT_TIMESTAMP not null on update CURRENT_TIMESTAMP comment 'The time the record was last modified',
    ip             varchar(255)                        not null comment 'Example: 134.201.250.155',
    hostname       varchar(255)                        not null comment 'Example: 134.201.250.155',
    type           varchar(255)                        not null comment 'Example: ipv4',
    continent_code varchar(255)                        not null comment 'Example: NA',
    continent_name varchar(255)                        not null comment 'Example: North America',
    country_code   varchar(255)                        not null comment 'Example: US',
    country_name   varchar(255)                        not null comment 'Example: United States',
    region_code    varchar(255)                        not null comment 'Example: CA',
    region_name    varchar(255)                        not null comment 'Example: California',
    city           varchar(255)                        not null comment 'Example: Los Angeles',
    zip            varchar(255)                        not null comment 'Example: 90013',
    latitude       double                              not null comment 'Example: 34.0453',
    longitude      double                              not null comment 'Example: -118.2413',
    location       longtext                            not null comment 'Example: {geoname_id:5368361,capital:Washington D.C.,languages:[{code:en,name:English,native:English}],country_flag:https://assets.ipstack.com/images/assets/flags_svg/us.svg,country_flag_emoji:ud83cuddfaud83cuddf8,country_flag_emoji_unicode:U+1F1FA U+1F1F8,calling_code:1,is_eu:false}',
    time_zone      longtext                            not null comment 'Example: {id:America/Los_Angeles,current_time:2018-03-29T07:35:08-07:00,gmt_offset:-25200,code:PDT,is_daylight_saving:true}',
    currency       longtext                            not null comment 'Example: {code:USD,name:US Dollar,plural:US dollars,symbol:$,symbol_native:$}',
    connection     longtext                            not null comment 'Example: {asn:25876,isp:Los Angeles Department of Water & Power}',
    security       longtext                            not null comment 'Example: {is_proxy:false,proxy_type:null,is_crawler:false,crawler_name:null,crawler_type:null,is_tor:false,threat_level:low,threat_types:null}',
    constraint ip_data_ip_uindex
        unique (ip)
);

