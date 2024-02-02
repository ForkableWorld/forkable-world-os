create table quantimodo_test.cards
(
    action_sheet_buttons text                                null,
    avatar               varchar(100)                        null,
    avatar_circular      varchar(100)                        null,
    background_color     varchar(20)                         null,
    buttons              text                                null,
    client_id            varchar(80)                         not null,
    content              text                                null,
    created_at           timestamp default CURRENT_TIMESTAMP not null,
    deleted_at           timestamp                           null,
    header_title         varchar(100)                        null,
    html                 text                                null,
    html_content         text                                null,
    element_id           varchar(80)                         not null,
    image                varchar(100)                        null,
    input_fields         text                                null,
    intent_name          varchar(80)                         null,
    ion_icon             varchar(20)                         null,
    link                 varchar(2083)                       null comment 'Link field is deprecated due to ambiguity.  Please use url field instead.',
    parameters           text                                null,
    sharing_body         text                                null,
    sharing_buttons      text                                null,
    sharing_title        varchar(80)                         null,
    sub_header           varchar(80)                         null,
    sub_title            varchar(80)                         null,
    title                varchar(80)                         null,
    type                 varchar(80)                         not null,
    updated_at           timestamp default CURRENT_TIMESTAMP not null on update CURRENT_TIMESTAMP,
    user_id              bigint unsigned                     not null,
    url                  varchar(2083)                       null comment 'URL to go to when the card is clicked',
    id                   int                                 not null
        primary key,
    slug                 varchar(200)                        null comment 'The slug is the part of a URL that identifies a page in human-readable keywords.',
    constraint cards_slug_uindex
        unique (slug),
    constraint cards_client_id_fk
        foreign key (client_id) references quantimodo_test.oa_clients (client_id),
    constraint cards_user_id_fk
        foreign key (user_id) references quantimodo_test.wp_users (ID)
)
    charset = utf8mb3;

