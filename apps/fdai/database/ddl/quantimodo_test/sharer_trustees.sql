create table quantimodo_test.sharer_trustees
(
    id                int unsigned auto_increment
        primary key,
    sharer_user_id    bigint unsigned                                                         not null comment 'The sharer who has granted data access to the trustee. ',
    trustee_user_id   bigint unsigned                                                         not null comment 'The trustee who has been granted access to the sharer data.',
    scopes            varchar(2000)                                                           not null comment 'Whether the trustee has read access and/or write access to the data.',
    relationship_type enum ('patient-physician', 'student-teacher', 'child-parent', 'friend') not null,
    created_at        timestamp default CURRENT_TIMESTAMP                                     not null,
    updated_at        timestamp default CURRENT_TIMESTAMP                                     not null on update CURRENT_TIMESTAMP,
    deleted_at        timestamp                                                               null,
    constraint sharer_user_id_trustee_user_id_uindex
        unique (sharer_user_id, trustee_user_id),
    constraint sharer_trustees_wp_users_ID_fk
        foreign key (sharer_user_id) references quantimodo_test.wp_users (ID),
    constraint sharer_trustees_wp_users_ID_fk_2
        foreign key (trustee_user_id) references quantimodo_test.wp_users (ID)
)
    charset = latin1;

