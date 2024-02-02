create table quantimodo_test.sent_emails
(
    id            int unsigned auto_increment
        primary key,
    user_id       bigint unsigned                     null,
    type          varchar(100)                        not null,
    created_at    timestamp default CURRENT_TIMESTAMP not null,
    updated_at    timestamp default CURRENT_TIMESTAMP not null on update CURRENT_TIMESTAMP,
    deleted_at    timestamp                           null,
    client_id     varchar(255)                        null,
    slug          varchar(100)                        null,
    response      varchar(140)                        null,
    content       text                                null,
    wp_post_id    bigint unsigned                     null,
    email_address varchar(255)                        null,
    subject       varchar(78)                         not null comment 'A Subject Line is the introduction that identifies the emails intent.
                    This subject line, displayed to the email user or recipient when they look at their list of messages in their inbox,
                    should tell the recipient what the message is about, what the sender wants to convey.',
    constraint sent_emails_client_id_fk
        foreign key (client_id) references quantimodo_test.oa_clients (client_id),
    constraint sent_emails_user_id_fk
        foreign key (user_id) references quantimodo_test.wp_users (ID),
    constraint sent_emails_wp_posts_ID_fk
        foreign key (wp_post_id) references quantimodo_test.wp_posts (ID)
)
    charset = utf8mb3;

create index sent_emails_user_id_type_index
    on quantimodo_test.sent_emails (user_id, type);

