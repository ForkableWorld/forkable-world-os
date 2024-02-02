create table quantimodo_test.button_clicks
(
    card_id      varchar(80)                         not null,
    button_id    varchar(80)                         not null,
    client_id    varchar(80)                         not null,
    created_at   timestamp default CURRENT_TIMESTAMP not null,
    deleted_at   timestamp                           null,
    id           int auto_increment
        primary key,
    input_fields text                                null,
    intent_name  varchar(80)                         null,
    parameters   text                                null,
    updated_at   timestamp default CURRENT_TIMESTAMP not null on update CURRENT_TIMESTAMP,
    user_id      bigint unsigned                     not null,
    constraint button_clicks_client_id_fk
        foreign key (client_id) references quantimodo_test.oa_clients (client_id),
    constraint button_clicks_user_id_fk
        foreign key (user_id) references quantimodo_test.wp_users (ID)
)
    charset = utf8mb3;

