<?php

namespace LimeSurvey\Api\Transformer\Validators;

/**
 * Example config:
 * 'googleAnalyticsApiKey' => ['pattern' => '/^[a-zA-Z\-\d]*$/']
 */
class RegexValidator implements ValidatorInterface
{
    private string $name = 'pattern';

    /**
     * @param string $key
     * @param mixed $value
     * @param array $config
     * @param array $data
     * @param array $options
     * @return array|bool
     */
    public function validate($key, $value, $config, $data, $options = [])
    {
        $messages = [];
        $config[$this->name] = $this->normaliseConfigValue($config, $options);
        if ($config[$this->name] !== false && !empty($value)) {
            $result = $this->validateByPattern($config[$this->name], $value);
            if (is_string($result)) {
                $messages[] = $result;
            }
        }

        return empty($messages) ? true : $messages;
    }

    /**
     * Executes the actual validation, factored out,
     * so it can be used by other validators
     * @param string $pattern
     * @param mixed $value
     * @return bool|string
     */
    public function validateByPattern($pattern, $value)
    {
        $matched = true;
        $match = preg_match($pattern, $value);
        if ($match !== 1) {
            $matched = $value . " doesn't match expected pattern.";
        }
        return $matched;
    }

    public function normaliseConfigValue(
        $config,
        $options = []
    ) {
        return $config[$this->name] ?? $this->getDefaultConfig();
    }

    public function getDefaultConfig()
    {
        return false;
    }
}
