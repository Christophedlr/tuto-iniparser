<?php

/**
 * Class IniParser
 */
class IniParser
{
    private $parsedIni = [];
    private $actualSection = '';
    private $filename = '';

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
                if (isset($this->parsedIni[strtoupper($matches[1])])) {
                    throw new Exception('Duplicate section');
                }

                $this->actualSection = strtoupper($matches[1]);
            } else if (preg_match('#([a-zA-Z_]{1,})=(.*)#', $line, $matches)) {
                if (isset($this->parsedIni[$this->actualSection][strtolower($matches[1])])) {
                    throw new Exception('Duplicate key');
                }

                $key = strtolower($matches[1]);
                $value = preg_replace('# ;.*#', '', $matches[2]);

                if (substr($value, 0, 1) === '"' && substr($value, -1) === '"') {
                    $value = substr($value, 1, -1);
                }
                $this->parsedIni[$this->actualSection][$key] = $value;
            }
        }

        fclose($handle);

        $this->filename = $filename;
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
        if (isset($this->parsedIni[strtoupper($section)][strtolower($key)])) {
            return $this->parsedIni[strtoupper($section)][strtolower($key)];
        }

        return '';
    }

    /**
     * Renvoi la valeur au format nomnbre entier
     *
     * @param string $section
     * @param string $key
     * @return int
     */
    public function getIntValue(string $section, string $key): int
    {
        return intval($this->getValue($section, $key));
    }

    /**
     * Renvoi la valeur au format nombre à virgule flottante
     *
     * @param string $section
     * @param string $key
     * @return float
     */
    public function getFloatValue(string $section, string $key): float
    {
        return floatval($this->getValue($section, $key));
    }

    /**
     * @param string $section
     * @param string $key
     * @return bool
     */
    public function getBoolValue(string $section, string $key): bool
    {
        $value = $this->getValue($section, $key);

        if (strtolower($value) === 'yes' || $value === true) {
            return true;
        } else if ($value === false) {
            return false;
        }

        return false;
    }

    /**
     * Regarde si la section existe
     *
     * @param string $section
     * @return bool
     */
    public function isSectionExist(string $section): bool
    {
        return isset($this->parsedIni[strtoupper($section)]);
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
        return isset($this->parsedIni[strtoupper($section)][strtolower($key)]);
    }

    /**
     * Ajoute ou modifie une valeur dans le fichier INI
     *
     * @param string $section
     * @param string $key
     * @param $val
     * @throws Exception
     */
    public function addValue(string $section, string $key, $val)
    {
        if (!preg_match('#([a-zA-Z_]{1,})#', $section, $matches)) {
            throw  new Exception('The section name selected to add, is not valid');
        }

        if (!preg_match('#([a-zA-Z_]{1,})#', $key, $matches)) {
            throw  new Exception('The key name selected to add, is not valid');
        }

        if (is_string($val)) {
            $val = sprintf('"%s"', $val);
        }
        $this->parsedIni[strtoupper($section)][strtolower($key)] = $val;
    }

    /**
     * Sauvegarde un nouveau fichier INI ou le fichier actuel
     *
     * @param string $filename
     */
    public function save(string $filename = '')
    {
        if (empty($filename)) {
            $filename = $this->filename;
        }

        $file = [];

        foreach ($this->parsedIni as $section => $array) {
            $file[] = '['.$section.']';

            foreach ($array as $key => $val) {
                if (is_bool($val)) {
                    if ($val) {
                        $val = 'yes';
                    } else {
                        $val = 'no';
                    }
                }
                $file[] = $key.'='.$val;
            }
        }

        file_put_contents($filename, implode("\n", $file));
    }
}
