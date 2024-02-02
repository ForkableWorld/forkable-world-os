create table quantimodo_test.love_reactant_reaction_counters
(
    id               bigint unsigned auto_increment
        primary key,
    reactant_id      bigint unsigned not null,
    reaction_type_id bigint unsigned not null,
    count            bigint unsigned not null,
    weight           decimal(13, 2)  not null,
    created_at       timestamp       null,
    updated_at       timestamp       null,
    constraint love_reactant_reaction_counters_reactant_id_foreign
        foreign key (reactant_id) references quantimodo_test.love_reactants (id)
            on delete cascade,
    constraint love_reactant_reaction_counters_reaction_type_id_foreign
        foreign key (reaction_type_id) references quantimodo_test.love_reaction_types (id)
            on delete cascade
)
    collate = utf8mb3_unicode_ci;

create index love_reactant_reaction_counters_reactant_reaction_type_index
    on quantimodo_test.love_reactant_reaction_counters (reactant_id, reaction_type_id);

