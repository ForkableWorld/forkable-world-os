create table quantimodo_test.love_reactant_reaction_totals
(
    id          bigint unsigned auto_increment
        primary key,
    reactant_id bigint unsigned not null,
    count       bigint unsigned not null,
    weight      decimal(13, 2)  not null,
    created_at  timestamp       null,
    updated_at  timestamp       null,
    constraint love_reactant_reaction_totals_reactant_id_foreign
        foreign key (reactant_id) references quantimodo_test.love_reactants (id)
            on delete cascade
)
    collate = utf8mb3_unicode_ci;

