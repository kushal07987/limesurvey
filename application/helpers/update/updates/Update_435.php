            $oTransaction = $oDB->beginTransaction();
            // Check if default survey groups exists - at some point it was possible to delete it
            $defaultSurveyGroupExists = $oDB->createCommand()
            ->select('gsid')
            ->from("{{surveys_groups}}")
            ->where('gsid = 1')
            ->queryScalar();
            if ($defaultSurveyGroupExists == false) {
                // Add missing default template
                $date = date("Y-m-d H:i:s");
                $oDB->createCommand()->insert('{{surveys_groups}}', array(
                    'gsid'        => 1,
                    'name'        => 'default',
                    'title'       => 'Default',
                    'description' => 'Default survey group',
                    'sortorder'   => '0',
                    'owner_id'   => '1',
                    'created'     => $date,
                    'modified'    => $date,
                    'created_by'  => '1'
                ));
            }
            $oDB->createCommand()->addColumn('{{surveys_groups}}', 'alwaysavailable', "boolean NULL");
            $oDB->createCommand()->update(
                '{{surveys_groups}}',
                array(
                    'alwaysavailable' => '0',
                )
            );
            $oDB->createCommand()->update(
                '{{surveys_groups}}',
                array(
                    'alwaysavailable' => '0',
                ),
                "gsid=1"
            );
            $oDB->createCommand()->update('{{settings_global}}', array('stg_value' => 435), "stg_name='DBVersion'");
            $oTransaction->commit();
