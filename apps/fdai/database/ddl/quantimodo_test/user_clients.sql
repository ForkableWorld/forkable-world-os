create table quantimodo_test.user_clients
(
    id                      int auto_increment
        primary key,
    client_id               varchar(80)                         null,
    created_at              timestamp default CURRENT_TIMESTAMP not null,
    deleted_at              timestamp                           null,
    earliest_measurement_at timestamp                           null comment 'Earliest measurement time for this variable and client',
    latest_measurement_at   timestamp                           null comment 'Earliest measurement time for this variable and client',
    number_of_measurements  int unsigned                        null,
    updated_at              timestamp default CURRENT_TIMESTAMP not null on update CURRENT_TIMESTAMP,
    user_id                 bigint unsigned                     not null,
    constraint user
        unique (user_id, client_id),
    constraint user_clients_client_id_fk
        foreign key (client_id) references quantimodo_test.oa_clients (client_id),
    constraint user_clients_user_id_fk
        foreign key (user_id) references quantimodo_test.wp_users (ID)
)
    charset = utf8mb3;

