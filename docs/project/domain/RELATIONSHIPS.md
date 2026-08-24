# Catalog Relationship Contract

Status: canonical relationship vocabulary.

The implemented generic relationship store is `entity_relationships`. Allowed relationship type values are owned by `RelationshipType`: `performed_by`, `written_by`, `version_of`, `part_of`, and `related_to`.

A relationship identity consists of subject type/id, relationship type, and object type/id. New relationship values require an enum and contract change before persistence or UI code uses them.
