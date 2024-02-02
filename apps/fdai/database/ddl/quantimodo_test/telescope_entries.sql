create table quantimodo_test.telescope_entries
(
    sequence                bigint unsigned auto_increment
        primary key,
    uuid                    char(36)             not null,
    batch_id                char(36)             not null,
    family_hash             varchar(255)         null,
    should_display_on_index tinyint(1) default 1 not null,
    type                    varchar(20)          not null,
    content                 longtext             not null,
    created_at              datetime             null,
    constraint telescope_entries_uuid_unique
        unique (uuid)
)
    charset = utf8mb3;

create index telescope_entries_batch_id_index
    on quantimodo_test.telescope_entries (batch_id);

create index telescope_entries_family_hash_index
    on quantimodo_test.telescope_entries (family_hash);

create index telescope_entries_type_should_display_on_index_index
    on quantimodo_test.telescope_entries (type, should_display_on_index);

