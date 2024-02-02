create table quantimodo_test.wp_posts
(
    ID                    bigint unsigned auto_increment comment 'Unique number assigned to each post.'
        primary key,
    post_author           bigint unsigned default '0'                   null comment 'The user ID who created it.',
    post_date             datetime        default '0000-00-00 00:00:00' not null,
    post_date_gmt         datetime        default '0000-00-00 00:00:00' not null,
    post_content          longtext                                      null comment 'Holds all the content for the post, including HTML, shortcodes and other content.',
    post_title            text                                          null comment 'Title of the post.',
    post_excerpt          text                                          null comment 'Custom intro or short version of the content.',
    post_status           varchar(20)     default 'publish'             null comment 'Status of the post, e.g. ‘draft’, ‘pending’, ‘private’, ‘publish’. Also a great WordPress <a href="https://poststatus.com/" target="_blank">news site</a>.',
    comment_status        varchar(20)     default 'open'                null comment 'If comments are allowed.',
    ping_status           varchar(20)     default 'open'                null comment 'If the post allows <a href="http://codex.wordpress.org/Introduction_to_Blogging#Pingbacks" target="_blank">ping and trackbacks</a>.',
    post_password         varchar(255)                                  null comment 'Optional password used to view the post.',
    post_name             varchar(200)                                  null comment 'URL friendly slug of the post title.',
    to_ping               text                                          null comment 'A list of URLs WordPress should send pingbacks to when updated.',
    pinged                text                                          null comment 'A list of URLs WordPress has sent pingbacks to when updated.',
    post_modified         datetime        default '0000-00-00 00:00:00' not null,
    post_modified_gmt     datetime        default '0000-00-00 00:00:00' not null,
    post_content_filtered longtext                                      null comment 'Used by plugins to cache a version of post_content typically passed through the ‘the_content’ filter. Not used by WordPress core itself.',
    post_parent           bigint unsigned default '0'                   null comment 'Used to create a relationship between this post and another when this post is a revision, attachment or another type.',
    guid                  varchar(255)                                  null comment 'Global Unique Identifier, the permanent URL to the post, not the permalink version.',
    menu_order            int             default 0                     null comment 'Holds the display number for pages and other non-post types.',
    post_type             varchar(20)     default 'post'                null comment 'The content type identifier.',
    post_mime_type        varchar(100)                                  null comment 'Only used for attachments, the MIME type of the uploaded file.',
    comment_count         bigint          default 0                     null comment 'Total number of comments, pingbacks and trackbacks.',
    updated_at            timestamp       default CURRENT_TIMESTAMP     not null on update CURRENT_TIMESTAMP,
    created_at            timestamp       default CURRENT_TIMESTAMP     not null,
    deleted_at            timestamp                                     null,
    client_id             varchar(255)                                  null,
    record_size_in_kb     int                                           null,
    constraint wp_posts_wp_users_ID_fk
        foreign key (post_author) references quantimodo_test.wp_users (ID)
)
    comment 'The posts table is arguably the most important table in the WordPress database. Its name sometimes throws people who believe it purely contains their blog posts. However, albeit badly named, it is an extremely powerful table that stores various types of content including posts, pages, menu items, media attachments and any custom post types that a site uses.'
    charset = utf8mb3;

create index idx_wp_posts_post_author_post_modified_deleted_at
    on quantimodo_test.wp_posts (post_author, post_modified, deleted_at);

create index post_author
    on quantimodo_test.wp_posts (post_author);

create index post_name
    on quantimodo_test.wp_posts (post_name(191));

create index post_parent
    on quantimodo_test.wp_posts (post_parent);

create index type_status_date
    on quantimodo_test.wp_posts (post_type, post_status, post_date, ID);

create index wp_posts_post_modified_index
    on quantimodo_test.wp_posts (post_modified);

