create table quantimodo_test.wp_postmeta
(
    meta_id    bigint unsigned auto_increment comment 'Unique number assigned to each row of the table.'
        primary key,
    post_id    bigint unsigned default '0'               null comment 'The ID of the post the data relates to.',
    meta_key   varchar(255)                              null comment 'An identifying key for the piece of data.',
    meta_value longtext                                  null comment 'The actual piece of data.',
    updated_at timestamp       default CURRENT_TIMESTAMP not null on update CURRENT_TIMESTAMP,
    created_at timestamp       default CURRENT_TIMESTAMP not null,
    deleted_at timestamp                                 null,
    client_id  varchar(255)                              null,
    constraint wp_postmeta_wp_posts_ID_fk
        foreign key (post_id) references quantimodo_test.wp_posts (ID)
            on update cascade on delete cascade
)
    comment 'This table holds any extra information about individual posts. It is a vertical table using key/value pairs to store its data, a technique WordPress employs on a number of tables throughout the database allowing WordPress core, plugins and themes to store unlimited data.'
    charset = utf8mb3;

create index meta_key
    on quantimodo_test.wp_postmeta (meta_key(191));

create index post_id
    on quantimodo_test.wp_postmeta (post_id);

