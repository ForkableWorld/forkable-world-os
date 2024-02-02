create table quantimodo_test.love_reactions
(
    id               bigint unsigned auto_increment
        primary key,
    reactant_id      bigint unsigned not null,
    reacter_id       bigint unsigned not null,
    reaction_type_id bigint unsigned not null,
    rate             decimal(4, 2)   not null,
    created_at       timestamp       null,
    updated_at       timestamp       null,
    constraint love_reactions_reactant_id_foreign
        foreign key (reactant_id) references quantimodo_test.love_reactants (id)
            on delete cascade,
    constraint love_reactions_reacter_id_foreign
        foreign key (reacter_id) references quantimodo_test.love_reacters (id)
            on delete cascade,
    constraint love_reactions_reaction_type_id_foreign
        foreign key (reaction_type_id) references quantimodo_test.love_reaction_types (id)
            on delete cascade
)
    collate = utf8mb3_unicode_ci;

create index love_reactions_reactant_id_reacter_id_index
    on quantimodo_test.love_reactions (reactant_id, reacter_id);

create index love_reactions_reactant_id_reacter_id_reaction_type_id_index
    on quantimodo_test.love_reactions (reactant_id, reacter_id, reaction_type_id);

create index love_reactions_reactant_id_reaction_type_id_index
    on quantimodo_test.love_reactions (reactant_id, reaction_type_id);

create index love_reactions_reacter_id_reaction_type_id_index
    on quantimodo_test.love_reactions (reacter_id, reaction_type_id);

