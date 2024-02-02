create table quantimodo_test.oauth_personal_access_clients
(
    id         int unsigned auto_increment
        primary key,
    client_id  int unsigned not null,
    created_at timestamp    null,
    updated_at timestamp    null
)
    collate = utf8mb3_unicode_ci;

create index oauth_personal_access_clients_client_id_index
    on quantimodo_test.oauth_personal_access_clients (client_id);

