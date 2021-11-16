            $oTransaction = $oDB->beginTransaction();
            $oDB->createCommand()->insert(
                "{{plugins}}",
                [
                    'name' => 'ComfortUpdateChecker',
                    'plugin_type' => 'core',
                    'active' => 1,
                    'version' => '1.0.0',
                    'load_error' => 0,
                    'load_error_message' => null
                ]
            );
            $oDB->createCommand()->update('{{settings_global}}', array('stg_value' => 430), "stg_name='DBVersion'");
            $oTransaction->commit();
