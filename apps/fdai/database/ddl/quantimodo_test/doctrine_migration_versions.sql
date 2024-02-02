create table quantimodo_test.doctrine_migration_versions
(
    version    varchar(255)                        not null
        primary key,
    updated_at timestamp default CURRENT_TIMESTAMP not null on update CURRENT_TIMESTAMP,
    created_at timestamp default CURRENT_TIMESTAMP not null,
    deleted_at timestamp                           null,
    client_id  varchar(255)                        null
)
    charset = utf8mb3;

