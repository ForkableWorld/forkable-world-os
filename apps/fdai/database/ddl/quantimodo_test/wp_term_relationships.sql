create table quantimodo_test.wp_term_relationships
(
    object_id        bigint unsigned                     not null comment 'The ID of the post object.',
    term_taxonomy_id bigint unsigned                     not null comment 'The ID of the term / taxonomy pair.',
    term_order       int                                 null comment 'Allow ordering of terms for an object, not fully used.',
    updated_at       timestamp default CURRENT_TIMESTAMP not null on update CURRENT_TIMESTAMP,
    created_at       timestamp default CURRENT_TIMESTAMP not null,
    deleted_at       timestamp                           null,
    client_id        varchar(255)                        null,
    primary key (object_id, term_taxonomy_id),
    constraint wp_term_relationships_wp_posts_ID_fk
        foreign key (object_id) references quantimodo_test.wp_posts (ID)
            on update cascade on delete cascade,
    constraint wp_term_relationships_wp_term_taxonomy_term_taxonomy_id_fk
        foreign key (term_taxonomy_id) references quantimodo_test.wp_term_taxonomy (term_taxonomy_id)
)
    comment 'So far we have seen how terms and their taxonomies are stored in the database, but have yet to see how WordPress stores the critical data when it comes to using taxonomies. This post exists in wp_posts and when we actually assign the category and tags through the WordPress dashboard this is the <a href="http://en.wikipedia.org/wiki/Junction_table" target="_blank">junction table</a> that records that information. Each row defines a relationship between a post (object) in wp_posts and a term of a certain taxonomy in wp_term_taxonomy.'
    charset = utf8mb3;

create index term_taxonomy_id
    on quantimodo_test.wp_term_relationships (term_taxonomy_id);

