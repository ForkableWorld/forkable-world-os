create table quantimodo_test.taggables
(
    tag_id        int unsigned    not null,
    taggable_type varchar(255)    not null,
    taggable_id   bigint unsigned not null,
    constraint taggables_tag_id_taggable_id_taggable_type_unique
        unique (tag_id, taggable_id, taggable_type),
    constraint taggables_tag_id_foreign
        foreign key (tag_id) references quantimodo_test.tags (id)
            on delete cascade
)
    collate = utf8mb3_unicode_ci;

create index taggables_taggable_type_taggable_id_index
    on quantimodo_test.taggables (taggable_type, taggable_id);

