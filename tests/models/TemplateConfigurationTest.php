<?php

namespace ls\tests;

/**
 * @since 2017-06-13
 * @group tempconf
 * @group template
 */
class TemplateConfigurationTest extends TestBaseClass
{
    /**
     * Issue #12795.
     * @throws \CException
     */
    public function testCopyMinimalTemplate()
    {
        \Yii::import('application.helpers.globalsettings_helper', true);
        $tempConf = \TemplateConfiguration::getInstanceFromTemplateName('default');
        $tempConf->prepareTemplateRendering('default');

        // No PHP notices.
        $this->assertTrue(true);
    }

    public function testOptionsSanitization()
    {
        // Import lss
        $surveyFile = self::$surveysFolder . '/limesurvey_survey_666368.lss';
        self::importSurvey($surveyFile);

        $templateConfiguration = \TemplateConfiguration::checkAndcreateSurveyConfig(self::$surveyId);

        $options = [
            'realThemeFile' => 'themes/survey/fruity/files/logo.png',
            'realGeneralFile' => 'upload/themes/survey/generalfiles/index.html',
            'realRelativeThemeFile' => './files/logo.png',
            'virtualThemeFile' => 'image::theme::files/logo.png',
            'virtualGeneralFile' => 'image::generalfiles::index.html',
            'nonExistingFile' => 'test.png',
            'existingInvalidFile' => 'themes/admin/Apple_Blossom/images/Limesurvey_logo.png',
            'virtualPathWithTraversalInsideTheme' => 'image::theme::../fruity/files/logo.png',
            'virtualPathWithTraversalOutsideTheme' => 'image::theme::../vanilla/files/logo.png',
        ];
        $templateConfiguration->options = json_encode($options);
        $templateConfiguration->save();

        $savedOptions = json_decode($templateConfiguration->options, true);

        // Check that valid "real" paths get transformed
        $this->assertEquals('image::theme::files/logo.png', $savedOptions['realThemeFile']);
        $this->assertEquals('image::generalfiles::index.html', $savedOptions['realGeneralFile']);
        $this->assertEquals('image::theme::files/logo.png', $savedOptions['realRelativeThemeFile']);

        // Check that valid "virtual" paths are only transformed to remove traversals
        $this->assertEquals($options['virtualThemeFile'], $savedOptions['virtualThemeFile']);
        $this->assertEquals($options['virtualGeneralFile'], $savedOptions['virtualGeneralFile']);
        $this->assertEquals('image::theme::files/logo.png', $savedOptions['virtualPathWithTraversalInsideTheme']);

        // Check that invalid paths (real or virtual paths pointing to existing files outside of the allowed folders) are marked as invalid
        $this->assertEquals('invalid:' . $options['existingInvalidFile'], $savedOptions['existingInvalidFile']);
        $this->assertEquals('invalid:' . $options['virtualPathWithTraversalOutsideTheme'], $savedOptions['virtualPathWithTraversalOutsideTheme']);

        // Check that non-paths (values that don't match an existing file) are not changed
        $this->assertEquals($options['nonExistingFile'], $savedOptions['nonExistingFile']);
    }

    public function testImageSrc()
    {
        $templateConfiguration =  \Template::getInstance('fruity', null, null, false);

        $options = [
            'realThemeFile' => 'themes/survey/fruity/files/logo.png',
            'realGeneralFile' => 'upload/themes/survey/generalfiles/index.html',
            'realRelativeThemeFile' => './files/logo.png',
            'virtualThemeFile' => 'image::theme::files/logo.png',
            'virtualGeneralFile' => 'image::generalfiles::index.html',
            'nonExistingFile' => 'test.png',
            'existingInvalidFile' => 'themes/admin/Apple_Blossom/images/Limesurvey_logo.png',
            'virtualPathWithTraversalInsideTheme' => 'image::theme::../fruity/files/logo.png',
            'virtualPathWithTraversalOutsideTheme' => 'image::theme::../vanilla/files/logo.png',
        ];

        $output = \Yii::app()->twigRenderer->convertTwigToHtml('{{ imageSrc(realThemeFile) }}', $options, $templateConfiguration);
        $this->assertEmpty($output);

        $output = \Yii::app()->twigRenderer->convertTwigToHtml('{{ imageSrc(realGeneralFile) }}', $options, $templateConfiguration);
        $this->assertEmpty($output);

        $output = \Yii::app()->twigRenderer->convertTwigToHtml('{{ imageSrc(realRelativeThemeFile) }}', $options, $templateConfiguration);
        $this->assertNotEmpty($output);

        $output = \Yii::app()->twigRenderer->convertTwigToHtml('{{ imageSrc(virtualThemeFile) }}', $options, $templateConfiguration);
        $this->assertNotEmpty($output);

        $output = \Yii::app()->twigRenderer->convertTwigToHtml('{{ imageSrc(nonExistingFile) }}', $options, $templateConfiguration);
        $this->assertEmpty($output);

        $output = \Yii::app()->twigRenderer->convertTwigToHtml('{{ imageSrc(existingInvalidFile) }}', $options, $templateConfiguration);
        $this->assertEmpty($output);

        $output = \Yii::app()->twigRenderer->convertTwigToHtml('{{ imageSrc(virtualPathWithTraversalInsideTheme) }}', $options, $templateConfiguration);
        $this->assertNotEmpty($output);

        $output = \Yii::app()->twigRenderer->convertTwigToHtml('{{ imageSrc(virtualPathWithTraversalOutsideTheme) }}', $options, $templateConfiguration);
        $this->assertEmpty($output);

        // There is no general image file uploaded but, when setting a valid default,
        // imageSrc will only return false if the file is found and is not a valid image.
        // We are passing an 'index.html' file here so, imageSrc returning false means that
        // the file is found but not an image.
        $output = \Yii::app()->twigRenderer->convertTwigToHtml('{{ imageSrc(virtualGeneralFile, "files/logo.png") is same as(false) ? "OK" : "" }}', $options, $templateConfiguration);
        $this->assertEquals("OK", $output);
    }
}
