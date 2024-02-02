create table quantimodo_test.buttons
(
    accessibility_text     varchar(100)                        null,
    action                 varchar(20)                         null,
    additional_information varchar(20)                         null,
    client_id              varchar(80)                         not null,
    color                  varchar(20)                         null,
    confirmation_text      varchar(100)                        null,
    created_at             timestamp default CURRENT_TIMESTAMP not null,
    deleted_at             timestamp                           null,
    function_name          varchar(20)                         null,
    function_parameters    text                                null,
    html                   varchar(200)                        null,
    element_id             varchar(80)                         not null,
    image                  varchar(100)                        null,
    input_fields           text                                null,
    ion_icon               varchar(20)                         null,
    link                   varchar(100)                        null,
    state_name             varchar(20)                         null,
    state_params           text                                null,
    success_alert_body     varchar(200)                        null,
    success_alert_title    varchar(80)                         null,
    success_toast_text     varchar(80)                         null,
    text                   varchar(80)                         null,
    title                  varchar(80)                         null,
    tooltip                varchar(80)                         null,
    type                   varchar(80)                         not null,
    updated_at             timestamp default CURRENT_TIMESTAMP not null on update CURRENT_TIMESTAMP,
    user_id                bigint unsigned                     not null,
    id                     int auto_increment
        primary key,
    slug                   varchar(200)                        null comment 'The slug is the part of a URL that identifies a page in human-readable keywords.',
    constraint buttons_id_uindex
        unique (id),
    constraint buttons_slug_uindex
        unique (slug),
    constraint buttons_client_id_fk
        foreign key (client_id) references quantimodo_test.oa_clients (client_id),
    constraint buttons_user_id_fk
        foreign key (user_id) references quantimodo_test.wp_users (ID)
)
    charset = utf8mb3;

