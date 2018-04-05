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

        $this->parsedIni[$section][$key] = $val;
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
                $file[] = $key.'='.$val;
            }
        }

        //var_dump($file);
        file_put_contents($filename, implode("\n", $file));
    }
}
