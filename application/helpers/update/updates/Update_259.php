            $oTransaction = $oDB->beginTransaction();
            $oDB->createCommand()->createTable(
                '{{notifications}}',
                array(
                    'id' => 'pk',
                    'entity' => 'string(15) not null',
                    'entity_id' => 'integer not null',
                    'title' => 'string not null', // varchar(255) in postgres
                    'message' => 'text not null',
                    'status' => "string(15) not null default 'new' ",
                    'importance' => 'integer not null default 1',
                    'display_class' => "string(31) default 'default'",
                    'created' => 'datetime',
                    'first_read' => 'datetime'
                )
            );
            $oDB->createCommand()->createIndex(
                '{{notif_index}}',
                '{{notifications}}',
                'entity, entity_id, status',
                false
            );
            $oDB->createCommand()->update('{{settings_global}}', array('stg_value' => 259), "stg_name='DBVersion'");
            $oTransaction->commit();
