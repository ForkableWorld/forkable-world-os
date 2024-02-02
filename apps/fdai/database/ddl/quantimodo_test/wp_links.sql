create table quantimodo_test.wp_links
(
    link_id          bigint unsigned auto_increment comment 'Unique number assigned to each row of the table.'
        primary key,
    link_url         varchar(760)                                  not null comment 'Unique universal resource locator for the link.',
    link_name        varchar(255)                                  null comment 'Name of the link.',
    link_image       varchar(255)                                  null comment 'URL of an image related to the link.',
    link_target      varchar(25)                                   null comment 'The target frame for the link. e.g. _blank, _top, _none.',
    link_description varchar(255)                                  null comment 'Description of the link.',
    link_visible     varchar(20)     default 'Y'                   null comment 'Control if the link is public or private.',
    link_owner       bigint unsigned default '1'                   null comment 'ID of user who created the link.',
    link_rating      int             default 0                     null comment 'Add a rating between 0-10 for the link.',
    link_updated     datetime        default '0000-00-00 00:00:00' not null,
    link_rel         varchar(255)                                  null comment 'Relationship of link.',
    link_notes       mediumtext                                    null comment 'Notes about the link.',
    link_rss         varchar(255)    default ''                    not null,
    updated_at       timestamp       default CURRENT_TIMESTAMP     not null on update CURRENT_TIMESTAMP,
    created_at       timestamp       default CURRENT_TIMESTAMP     not null,
    deleted_at       timestamp                                     null,
    client_id        varchar(255)                                  null,
    constraint wp_links_link_url_uindex
        unique (link_url),
    constraint wp_links_wp_users_ID_fk
        foreign key (link_owner) references quantimodo_test.wp_users (ID)
)
    comment 'During the rise of popularity of blogging having a blogroll (links to other sites) on your site was very much in fashion. This table holds all those links for you.'
    charset = utf8mb3;

create index link_visible
    on quantimodo_test.wp_links (link_visible);

