<?php

// axe d'amélioration : pour les probas de départ, prendre la valeur la plus aventageuse et pas simplement la suivante 

$mesDes = array(4, 2, 2, 1, 1);
$nbDes  = 5;
echo "Ma main : ";
print_r($mesDes);
echo "<br>";

$probaLimite = 66;
$probaMenteur = 30;

function binomialCoeff($n, $k)
{
    if ($k === 0) {
        return 1;
    } else if ($n && $k) {
        $coeff = 1;
        for ($x = $n - $k + 1; $x <= $n; $x++) {
            $coeff *= $x;
        }
        for ($x = 1; $x <= $k; $x++) {
            $coeff /= $x;
        }
        return $coeff;
    }
}

function binomial($paco, $totalDes, $chiffreTest)
{
    if (!$paco) {
        return binomialCoeff($totalDes, $chiffreTest) * pow((2 / 6), $chiffreTest) * pow((4 / 6), ($totalDes - $chiffreTest));
    } else {
        return binomialCoeff($totalDes, $chiffreTest) * pow((1 / 6), $chiffreTest) * pow((5 / 6), ($totalDes - $chiffreTest));
    }
}

function proba($paco, $totalDes, $chiffreTest)
{
    if (!$paco) {
        $temp = 1;
        for ($cpt = 0; $cpt < $chiffreTest; $cpt++) {
            $temp -= binomial(false, $totalDes, $cpt);
        }
    } else {
        $temp = 1;
        for ($cpt = 0; $cpt < $chiffreTest; $cpt++) {
            $temp -= binomial(true, $totalDes, $cpt);
        }
    }
    return round($temp * 100, 3);
}

class chiffre
{
    public $valeur;
    public $nbOcurrence;

    public function __construct($val, $Occu)
    {
        $this->valeur = $val;
        $this->nbOcurrence = $Occu;
    }

    public function getValeur()
    {
        return $this->valeur;
    }

    public function getOccurence()
    {
        return $this->nbOcurrence;
    }
}

function decision($totalDes, $valeurTest, $quantiteTest)
{
    global $nbDes;
    global $mesDes;
    global $probaLimite;
    global $probaMenteur;

    $statMain = array_count_values($mesDes);
    for ($i = 0; $i < 5; $i++) {
        if (!isset($statMain[$i])) {
            $statMain[$i] = 0;
        }
    }
    asort($statMain);
    print_r($statMain);

    $meilleurChiffre = new chiffre(array_search(max($statMain), $statMain), max($statMain));

    $nbPaco = $statMain[1];

    $nbChiffreTest = $statMain[$valeurTest];

    $nbDesToTest = $totalDes - $nbDes;

    //  Cas ou l'on ne joue pas en paco
    if ($valeurTest !== 1) {
        echo "<br>";
        echo "On joue actuellement en $quantiteTest x $valeurTest avec $totalDes dès dont $nbDes dans notre main <br>";
        echo "Il y a un proba de ";
        echo proba(true, $nbDesToTest, $quantiteTest - $nbPaco);
        echo " % qu'il y ai au moins ";
        echo $quantiteTest;
        echo " paco sachant que nous en avons $nbPaco dans notre main <br>";
        echo "Il y a une proba de ";
        echo proba(false, $nbDesToTest, $quantiteTest - $nbChiffreTest + 1);
        echo " % qu'il y ai au moins ";
        echo $quantiteTest + 1;
        echo " x $valeurTest sachant que nous en avons $nbChiffreTest dans notre main <br>";
        echo "Il y a une proba de ";
        echo proba(false, $nbDesToTest, $quantiteTest - $statMain[$valeurTest + 1]);
        echo " % qu'il y ai au moins $quantiteTest x ";
        echo $valeurTest + 1;
        echo " sachant que nous en avons ";
        echo $statMain[$valeurTest + 1];
        echo " dans notre main <br>";

        $probaPacoNormal = proba(true, $nbDesToTest, $quantiteTest - $nbPaco);
        $probaQuantiteNormal = proba(false, $nbDesToTest, $quantiteTest - $nbChiffreTest + 1);
        if ($valeurTest === 6) {
            $probaValeurNormal = 0;
        } else {
            $probaValeurNormal = proba(false, $nbDesToTest, $quantiteTest - $statMain[$valeurTest + 1]);
        }

        if ($probaPacoNormal > $probaMenteur || $probaQuantiteNormal > $probaMenteur || $probaValeurNormal > $probaMenteur) {
            if ($probaPacoNormal > $probaQuantiteNormal && $probaPacoNormal > $probaValeurNormal) {
                echo "On joue en paco";
                $cptPacoNormal = $quantiteTest;
                while (proba(true, $nbDesToTest, $cptPacoNormal) > $probaLimite) {
                    $cptPacoNormal++;
                    echo "<br>";
                    echo "proba de $cptPacoNormal parmis $nbDesToTest : ";
                    echo proba(true, $nbDesToTest, $cptPacoNormal);
                    echo "%";
                }
                if ($cptPacoNormal === $quantiteTest) {
                    echo "<br>";
                    echo "nb pacos à jouer : ";
                    echo $quantiteTest + 1;
                } else {
                    $cptPacoNormal--;
                    echo "<br>";
                    echo "nb pacos à jouer : ";
                    echo $cptPacoNormal + $nbPaco;
                }
            } else if ($probaQuantiteNormal > $probaPacoNormal && $probaQuantiteNormal > $probaValeurNormal) {
                echo "On augmente la quantité";
                echo "<br>";
                $cptQuantiteNormal = $quantiteTest;
                while (proba(false, $nbDesToTest, $cptQuantiteNormal) > $probaLimite) {
                    $cptQuantiteNormal++;
                    echo "<br>";
                    echo "proba de $cptQuantiteNormal parmis $nbDesToTest : ";
                    echo proba(false, $nbDesToTest, $cptQuantiteNormal);
                    echo " %";
                }
                if ($cptQuantiteNormal === $quantiteTest) {
                    echo "<br>";
                    echo "result : ";
                    echo $quantiteTest + 1;
                } else {
                    $cptQuantiteNormal--;
                    echo "<br>";
                    echo "result : ";
                    echo $cptQuantiteNormal + $nbChiffreTest;
                }
            } else {
                echo "On augmente la valeur";
                echo "<br>";
                // define what's the best value to play with
                foreach (array_keys($statMain) as $valeur) {
                    if ($valeur > $valeurTest) {
                        $valeurToPlay = $valeur;
                    }
                }
                echo "valeur à jouer : $valeurToPlay";
                $cptValeurNormal = $quantiteTest;
                while (proba(false, $nbDesToTest, $cptValeurNormal) > $probaLimite) {
                    $cptValeurNormal++;
                    echo "<br>";
                    echo "proba de $cptValeurNormal parmis $nbDesToTest : ";
                    echo proba(false, $nbDesToTest, $cptValeurNormal);
                    echo " %";
                }

                if ($cptValeurNormal === $ValeurTest) {
                    echo "<br>";
                    echo "result : ";
                    echo $ValeurTest + 1;
                } else {
                    $cptValeurNormal--;
                    echo "<br>";
                    echo "result : ";
                    echo $cptValeurNormal + $statMain[$valeurToPlay];
                }
            }
        } else {
            echo "menteur";
        }
    } else {
        echo "<br>";
        echo "On joue actuellement en $quantiteTest paco avec $totalDes dès dont $nbDes dans notre main <br>";
        echo "Il y a une proba de ";
        echo proba(true, $nbDesToTest, $quantiteTest + 1 - $nbPaco);
        echo "% qu'il y ait au moins ";
        echo $quantiteTest + 1;
        echo " pacos sachant que nous en avons $nbPaco dans notre main";
        echo "<br>";
        echo "il y a une proba de ";
        echo proba(false, $nbDesToTest, ceil($quantiteTest * 2) - $meilleurChiffre->getOccurence());
        echo "% qu'il y ait au moins ";
        echo ceil($quantiteTest * 2);
        echo " x ";
        echo $meilleurChiffre->getValeur();
        echo " sachant que nous en avons ";
        echo $meilleurChiffre->getOccurence();
        echo " dans notre main";
        echo "<br>";

        $probaPacoPaco = proba(true, $nbDesToTest, $quantiteTest + 1 - $nbPaco);
        $probaNormalPaco = proba(false, $nbDesToTest, ceil($quantiteTest * 2) - $meilleurChiffre->getOccurence());

        if ($probaPacoPaco > $probaMenteur || $probaNormalPaco > $probaMenteur) {
            if ($probaPacoPaco > $probaNormalPaco) {
                echo "on continue en paco";
                $cptPacoPaco = $quantiteTest;
                while (proba(true, $nbDesToTest, $cptPacoPaco) > $probaLimite) {
                    echo "<br>";
                    echo "proba de $cptPacoPaco pacos parmis $nbDesToTest : ";
                    echo proba(true, $nbDesToTest, $cptPacoPaco);
                    $cptPacoPaco++;
                }
                $cptPacoPaco--;
                echo "<br>";
                echo "quantite : ";
                if ($cptPacoPaco + $nbPaco > $quantiteTest) {
                    echo $cptPacoPaco + $nbPaco;
                } else {
                    echo $quantiteTest + 1;
                }
            } else {
                echo "on repasse en normal";
                $cptNormalPaco = $quantiteTest;
                while (proba(false, $nbDesToTest, $cptNormalPaco) > $probaLimite) {
                    echo "<br>";
                    echo "proba de $cptNormalPaco parmis $nbDesToTest : ";
                    echo proba(false, $nbDesToTest, $cptNormalPaco);
                    $cptNormalPaco++;
                }
                $cptNormalPaco--;

                echo "<br>";
                echo "valeur : ";
                echo $meilleurChiffre->getValeur();
                echo "<br>";
                echo "quantité : ";
                if ($cptNormalPaco + $statMain[$meilleurChiffre->getValeur()] > ceil($quantiteTest * 2)) {
                    echo $cptNormalPaco + $statMain[$meilleurChiffre->getValeur()];
                } else {
                    echo ceil($quantiteTest * 2);
                }
            }
        } else {
            echo "menteur";
        }
    }
}

$randValeur = random_int(1, 6);
$randQuantite = random_int(1, 10);
echo decision(20, $randValeur, $randQuantite);
