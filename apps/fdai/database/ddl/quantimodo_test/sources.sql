create table quantimodo_test.sources
(
    id         smallint unsigned auto_increment
        primary key,
    client_id  varchar(80)                         null,
    name       varchar(80)                         not null comment 'Name of the application or device',
    created_at timestamp default CURRENT_TIMESTAMP not null,
    updated_at timestamp default CURRENT_TIMESTAMP not null on update CURRENT_TIMESTAMP,
    deleted_at timestamp                           null,
    constraint name_UNIQUE
        unique (name)
)
    charset = utf8mb3;

create index sources_client_id_fk
    on quantimodo_test.sources (client_id);

