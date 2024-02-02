create table quantimodo_test.credentials
(
    user_id      bigint unsigned                       not null,
    connector_id int unsigned                          not null comment 'Connector id',
    attr_key     varchar(16)                           not null comment 'Attribute name such as token, userid, username, or password',
    attr_value   varbinary(3000)                       not null comment 'Encrypted value for the attribute specified',
    status       varchar(32) default 'UPDATED'         null,
    message      mediumtext                            null,
    expires_at   timestamp                             null,
    created_at   timestamp   default CURRENT_TIMESTAMP not null,
    updated_at   timestamp   default CURRENT_TIMESTAMP not null on update CURRENT_TIMESTAMP,
    deleted_at   timestamp                             null,
    client_id    varchar(255)                          null,
    primary key (user_id, connector_id, attr_key),
    constraint credentials_client_id_fk
        foreign key (client_id) references quantimodo_test.oa_clients (client_id),
    constraint credentials_user_id_fk
        foreign key (user_id) references quantimodo_test.wp_users (ID)
)
    charset = utf8mb3;

create index IDX_status_expires_connector
    on quantimodo_test.credentials (connector_id, expires_at, status);

