create table quantimodo_test.wp_bp_messages_notices
(
    id         bigint auto_increment
        primary key,
    subject    varchar(200)                         not null,
    message    longtext                             not null,
    date_sent  datetime                             not null,
    is_active  tinyint(1) default 0                 not null,
    updated_at timestamp  default CURRENT_TIMESTAMP not null on update CURRENT_TIMESTAMP,
    created_at timestamp  default CURRENT_TIMESTAMP not null,
    deleted_at timestamp                            null,
    client_id  varchar(255)                         null
)
    charset = utf8mb3;

create index is_active
    on quantimodo_test.wp_bp_messages_notices (is_active);

