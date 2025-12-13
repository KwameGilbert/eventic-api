<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class CreateAwardsTables extends AbstractMigration
{
    /**
     * Change Method.
     *
     * Create tables for awards events functionality:
     * - award_categories: Categories within an awards event
     * - award_nominees: Nominees within each category
     * - award_votes: Votes cast for nominees (with payment tracking)
     */
    public function change(): void
    {
        // Create award_categories table
        $categoriesTable = $this->table("award_categories", [
            "id" => false,
            "primary_key" => ["id"],
            "engine" => "InnoDB",
            "encoding" => "utf8mb4",
            "collation" => "utf8mb4_unicode_ci",
        ]);

        $categoriesTable
            ->addColumn("id", "integer", ["identity" => true,"signed" => false,])
            ->addColumn("event_id", "integer", ["signed" => false,"null" => false,])
            ->addColumn("name", "string", ["limit" => 255,"null" => false,])
            ->addColumn("image", "text", ["null" => true, "default" => null,])
            ->addColumn("description", "text", ["null" => true, "default" => null,])
            ->addColumn("cost_per_vote", "decimal", ["precision" => 10,"scale" => 2,"default" => "1.00","null" => false,])
            ->addColumn("voting_start", "datetime", ["null" => true,])
            ->addColumn("voting_end", "datetime", ["null" => true,])
            ->addColumn("status", "enum" , ["values" => ["active", "deactivated"], "default" => "active", "null" => false,])
            ->addColumn("display_order", "integer", ["default" => 0,"null" => false,])
            ->addColumn("created_at", "timestamp", ["default" => "CURRENT_TIMESTAMP","null" => true,])
            ->addColumn("updated_at", "timestamp", ["default" => "CURRENT_TIMESTAMP","update" => "CURRENT_TIMESTAMP","null" => true,])
            ->addIndex(["event_id"])
            ->addForeignKey("event_id", "events", "id", ["delete" => "CASCADE","update" => "CASCADE",])
            ->create();

        // Create award_nominees table
        $nomineesTable = $this->table("award_nominees", [
            "id" => false,
            "primary_key" => ["id"],
            "engine" => "InnoDB",
            "encoding" => "utf8mb4",
            "collation" => "utf8mb4_unicode_ci",
        ]);

        $nomineesTable
            ->addColumn("id", "integer", ["identity" => true,"signed" => false,])
            ->addColumn("category_id", "integer", ["signed" => false,"null" => false,])
            ->addColumn("event_id", "integer", ["signed" => false,"null" => false,])
            ->addColumn("name", "string", ["limit" => 255,"null" => false,])
            ->addColumn("description", "text", ["null" => true,])
            ->addColumn("image", "string", ["limit" => 255,"null" => true,])
            ->addColumn("display_order", "integer", ["default" => 0,"null" => false,])
            ->addColumn("created_at", "timestamp", ["default" => "CURRENT_TIMESTAMP","null" => true,])
            ->addColumn("updated_at", "timestamp", ["default" => "CURRENT_TIMESTAMP","update" => "CURRENT_TIMESTAMP","null" => true,])
            ->addIndex(["category_id"])
            ->addIndex(["event_id"])
            ->addForeignKey("category_id", "award_categories", "id", ["delete" => "CASCADE","update" => "CASCADE",])
            ->addForeignKey("event_id", "events", "id", ["delete" => "CASCADE","update" => "CASCADE",
            ])
            ->create();

        // Create award_votes table
        $votesTable = $this->table("award_votes", [
            "id" => false,
            "primary_key" => ["id"],
            "engine" => "InnoDB",
            "encoding" => "utf8mb4",
            "collation" => "utf8mb4_unicode_ci",
        ]);

        $votesTable
            ->addColumn("id", "integer", ["identity" => true, "signed" => false,])
            ->addColumn("nominee_id", "integer", ["signed" => false, "null" => false,])
            ->addColumn("category_id", "integer", ["signed" => false,"null" => false,])
            ->addColumn("event_id", "integer", ["signed" => false,"null" => false,])
            ->addColumn("number_of_votes", "integer", ["signed" => false,"null" => false,])
            ->addColumn("status", "enum", ["values" => ["pending", "paid"], "default" => "pending", "null" => false,])
            ->addColumn("reference", "text", ["null" => false,])
            ->addColumn("voter_name", "string", ["limit" => 255, "null" => true,])
            ->addColumn("voter_email", "string", ["limit" => 255, "null" => true,])
            ->addColumn("voter_phone", "string", ["limit" => 255, "null" => true,])
            ->addColumn("created_at", "timestamp", ["default" => "CURRENT_TIMESTAMP", "null" => true,])
            ->addColumn("updated_at", "timestamp", ["default" => "CURRENT_TIMESTAMP", "update" => "CURRENT_TIMESTAMP", "null" => true,])
            ->addIndex(["nominee_id"])
            ->addIndex(["category_id"])
            ->addIndex(["event_id"])
            ->addForeignKey("nominee_id", "award_nominees", "id", ["delete" => "CASCADE", "update" => "CASCADE",])
            ->addForeignKey("category_id", "award_categories", "id", ["delete" => "CASCADE", "update" => "CASCADE",])
            ->addForeignKey("event_id", "events", "id", ["delete" => "CASCADE", "update" => "CASCADE",])
            ->create();
    }

}
