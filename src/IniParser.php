<?php

/**
 * Class IniParser
 */
class IniParser
{
    private $parsedIni = [];
    private $actualSection = '';

    /**
     * IniParser constructor.
     * @param string $filename
     * @throws Exception
     */
    public function __construct(string $filename = '')
    {
        if ($filename !== '') {
            $this->load($filename);
        }
    }

    /**
     * Charge un fichier INI en mémoire
     *
     * @param string $filename
     * @throws Exception
     */
    public function load(string $filename)
    {
        if (!file_exists($filename)) {
            throw new Exception('The selected INI File not exist');
        }

        $handle = fopen($filename, 'r');

        while (!feof($handle)) {
            $line = fgets($handle);

            if (preg_match('#\[([a-zA-Z_]{1,})\]#', $line, $matches)) {
                if (isset($this->parsedIni[$matches[1]])) {
                    throw new Exception('Duplicate section');
                }

                $this->actualSection = $matches[1];
            } else if (preg_match('#([a-zA-Z_]{1,})=(.*)#', $line, $matches)) {
                if (isset($this->parsedIni[$this->actualSection][$matches[1]])) {
                    throw new Exception('Duplicate key');
                }

                $key = $matches[1];
                $value = preg_replace('# ;.*#', '', $matches[2]);

                $this->parsedIni[$this->actualSection][$key] = $value;
            }
        }

        fclose($handle);
    }

    /**
     * Récupère la valeur de la cké par rapport à la section, sinon renvoi une valeur vide
     *
     * @param string $section
     * @param string $key
     * @return string
     */
    public function getValue(string $section, string $key): string
    {
        if (isset($this->parsedIni[$section][$key])) {
            return $this->parsedIni[$section][$key];
        }

        return '';
    }

    /**
     * Regarde si la section existe
     *
     * @param string $section
     * @return bool
     */
    public function isSectionExist(string $section): bool
    {
        return isset($this->parsedIni[$section]);
    }

    /**
     * Regarde si la clé existe dans la section
     *
     * @param string $section
     * @param string $key
     * @return bool
     */
    public function isKeyExist(string $section, string $key): bool
    {
        return isset($this->parsedIni[$section][$key]);
    }
}
