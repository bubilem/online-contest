<?php

class Template
{

    private $name = '';

    private $data = [];

    public function __construct(string $name, array $data = [])
    {
        $this->setName($name);
        $this->setData($data);
    }

    public static function create(string $name, array $data = []): Template
    {
        return new Template($name, $data);
    }

    public function setName(string $name): Template
    {
        $this->name = $name;
        return $this;
    }

    public function getData(string $name)
    {
        if (isset($this->data[$name])) {
            return $this->data[$name];
        } else {
            return null;
        }
    }

    public function setData($data, $value = null): Template
    {
        if (is_array($data)) {
            $this->data = $data;
        } else if (!empty($data)) {
            $this->data[$data] = $value;
        }
        return $this;
    }

    public function addData(string $name, $value): Template
    {
        if ($value instanceof Template) {
            $value = strval($value);
        }
        if (!empty($name) && !empty($value)) {
            if (isset($this->data[$name])) {
                $this->data[$name] .= $value;
            } else {
                $this->data[$name] = $value;
            }
        }
        return $this;
    }

    /**
     * Clear template data
     *
     * @return Template
     */
    public function clearData(): Template
    {
        $this->data = [];
        return $this;
    }

    /**
     * Builds content for the web from a template and data
     *
     * @param string $tagBeginEndSymbols
     * @return string
     */
    public function render(string $tagBeginEndSymbols = "{}"): string
    {
        $templateContent = $this->getFileContent();
        $str = '';
        if (!empty($templateContent)) {
            if (strlen($tagBeginEndSymbols) != 2) {
                return $templateContent;
            }
            $layoutArray = Str::toArray($templateContent);
            $inData = false;
            $tag = '';
            foreach ($layoutArray as $char) {
                switch ($char) {
                    case $tagBeginEndSymbols[0]:
                        $inData = true;
                        $tag = '';
                        break;
                    case $tagBeginEndSymbols[1]:
                        if (isset($this->data[$tag])) {
                            $str .= $this->data[$tag];
                        }
                        $inData = false;
                        $tag = '';
                        break;
                    default:
                        if ($inData) {
                            $tag .= $char;
                        } else {
                            $str .= $char;
                        }
                        break;
                }
            }
        }
        return $str;
    }

    /**
     * Transform objec to string
     *
     * @return string
     */
    public function __toString()
    {
        return $this->render();
    }

    /**
     * Get file content
     *
     * @return string|false
     */
    private function getFileContent()
    {
        if (!empty($this->name) && file_exists('app/views/' . $this->name . '.phtml')) {
            return file_get_contents('app/views/' . $this->name . '.phtml');
        }
        return false;
    }
}
