create table quantimodo_test.phrases
(
    client_id                 varchar(80)                         not null,
    created_at                timestamp default CURRENT_TIMESTAMP not null,
    deleted_at                timestamp                           null,
    id                        int auto_increment
        primary key,
    image                     varchar(100)                        null,
    text                      text                                not null,
    title                     varchar(80)                         null,
    type                      varchar(80)                         not null,
    updated_at                timestamp default CURRENT_TIMESTAMP not null on update CURRENT_TIMESTAMP,
    url                       varchar(100)                        null,
    user_id                   bigint unsigned                     not null,
    responding_to_phrase_id   int                                 null,
    response_phrase_id        int                                 null,
    recipient_user_ids        text                                null,
    number_of_times_heard     int                                 null,
    interpretative_confidence double                              null,
    constraint phrases_client_id_fk
        foreign key (client_id) references quantimodo_test.oa_clients (client_id),
    constraint phrases_user_id_fk
        foreign key (user_id) references quantimodo_test.wp_users (ID)
)
    charset = utf8mb3;

