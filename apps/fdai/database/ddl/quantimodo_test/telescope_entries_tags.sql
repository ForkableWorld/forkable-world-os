create table quantimodo_test.telescope_entries_tags
(
    entry_uuid char(36)     not null,
    tag        varchar(255) not null,
    constraint telescope_entries_tags_entry_uuid_foreign
        foreign key (entry_uuid) references quantimodo_test.telescope_entries (uuid)
            on delete cascade
)
    charset = utf8mb3;

create index telescope_entries_tags_entry_uuid_tag_index
    on quantimodo_test.telescope_entries_tags (entry_uuid, tag);

create index telescope_entries_tags_tag_index
    on quantimodo_test.telescope_entries_tags (tag);

