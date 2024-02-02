create table quantimodo_test.oa_authorization_codes
(
    authorization_code varchar(40)                         not null
        primary key,
    client_id          varchar(80)                         not null,
    user_id            bigint unsigned                     not null,
    redirect_uri       varchar(2000)                       null,
    expires            timestamp                           null,
    scope              varchar(2000)                       null,
    updated_at         timestamp default CURRENT_TIMESTAMP not null on update CURRENT_TIMESTAMP,
    created_at         timestamp default CURRENT_TIMESTAMP not null,
    deleted_at         timestamp                           null,
    constraint oa_authorization_codes_client_id_fk
        foreign key (client_id) references quantimodo_test.oa_clients (client_id),
    constraint oa_authorization_codes_user_id_fk
        foreign key (user_id) references quantimodo_test.wp_users (ID)
)
    charset = utf8mb3;

