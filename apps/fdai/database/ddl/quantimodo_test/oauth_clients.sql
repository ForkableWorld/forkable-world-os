create table quantimodo_test.oauth_clients
(
    id                     int unsigned auto_increment
        primary key,
    user_id                bigint       null,
    name                   varchar(255) not null,
    secret                 varchar(100) not null,
    redirect               text         not null,
    personal_access_client tinyint(1)   not null,
    password_client        tinyint(1)   not null,
    revoked                tinyint(1)   not null,
    created_at             timestamp    null,
    updated_at             timestamp    null
)
    collate = utf8mb3_unicode_ci;

create index oauth_clients_user_id_index
    on quantimodo_test.oauth_clients (user_id);

