#!/usr/bin/php
<?php

include __DIR__.'/../autoloader.php';

use Vodka2\VKAudioToken\AndroidCheckin;
use Vodka2\VKAudioToken\SmallProtobufHelper;
use Vodka2\VKAudioToken\CommonParams;
use Vodka2\VKAudioToken\TokenReceiver;
use Vodka2\VKAudioToken\MTalkClient;

function print_help($file){
    echo
        "VK audio token receiver\n\n".
        "Usage: $file [options] vk_login vk_pass\n".
        "Options:\n".
        "-s file             - save GMS ID and token to the file\n".
        "-l file             - load GMS ID and token from file\n".
        "-g gms_id:gms_token - use specified GMS ID and token\n".
        "-d file             - use droidguard string from file\n".
        "                      instead of hardcoded one\n".
        "-m                  - make microG checkin\n".
        "                      by default checkin\n".
        "                      with droidguard string is made\n".
        "-h                  - print this help\n\n"
    ;
}

$scriptName = array_shift($argv);

if(count($argv) < 2){
    print_help($scriptName);
    exit(0);
}

function exit_err($str){
    echo "$str\n";
    exit(1);
}

function check_arg_exp($argv, $i){
    if($i == count($argv) - 1){
        exit_err("Expected argument after $argv[$i]");
    }
}

$useMicroGCheckin = false;

// Parsing arguments
for($i = 0; $i < count($argv); $i++){
    if($i == (count($argv) - 2)){
        $login = $argv[$i];
        $pass = $argv[$i + 1];
        break;
    }
    switch ($argv[$i]){
        case '-s':
            check_arg_exp($argv, $i);
            $i++;
            $gmsSaveFilePath = $argv[$i];
            break;
        case '-l':
            check_arg_exp($argv, $i);
            $i++;
            $gmsLoadFilePath = $argv[$i];
            if(!file_exists($gmsLoadFilePath)){
                echo "Warning: File $gmsLoadFilePath does not exist\n";
                unset($gmsLoadFilePath);
            }
            break;
        case '-g':
            check_arg_exp($argv, $i);
            $i++;
            $authData = explode(':', $argv[$i]);
            if(count($authData) != 2){
                exit_err("GMS ID and token must be separated with :");
            }
            $gmsId = $authData[0];
            $gmsToken = $authData[1];
            break;
        case '-d':
            check_arg_exp($argv, $i);
            $i++;
            $droidGuardStringFilePath = $argv[$i];
            if(!file_exists($droidGuardStringFilePath)){
                exit_err("File $droidGuardStringFilePath does not exist");
            }
            break;
        case '-h':
            print_help($scriptName);
            exit(0);
        case '-m':
            $useMicroGCheckin = true;
            break;
        default:
            exit_err("Unknown argument $argv[$i]");
    }
}

if(!isset($login)){
    exit_err("You must specify login and password");
}

if(!$useMicroGCheckin) {
    if (isset($droidGuardStringFilePath)) {
        echo "Reading droidguard string from $droidGuardStringFilePath\n\n";
    }

    $str24 = (isset($droidGuardStringFilePath)) ? file_get_contents($droidGuardStringFilePath) : 'CgYKrRCH-EjaB7kEAOuhCBKeE0ek5vRr4Be9xnS4Vz0IHDXjIuBVLAER1-zAJJd4RmYiBLRxHmQCFkEzCSZxv3Mb9l7R2ysQG97UYVZ5sEH_Mw78h5Xe29BZs_BUUFqU9GdrD26I6fyHoksEPl_eGuWZLgNnNy1nljzeT2iMkijNmJYX8eVjvRGKkHcTo3xocm0hZnyHhr6jAzcXUQG9Obj6MQ8zRJE--5npFTANt5ke4xJdctcpFDEro4rdgltEhbAb4qic1cPsSRmnRSLQsAcA1OqFqbWIogdlzfv4ow6C2IPUvS2_f0DZV6i40MYmT_SLzgduBdtdHVYi6zLGykRw1Cvd6MxOfYDsKji5m5oEkDxGFPR0kRBtZNM0opo-aK1LaIH7kzYybMJmmU2TBPcS_9abfZDS33GfbhkMtR3hSSclhyB4p7zilm2-34Ls2hMZVZhe9POvijQRQnY2mm_ooCCa-9vGAzSw6UqRc3OC2xfKx_JMZWTGsxwdFNs5xOIVJpoXzfmA6T_5b7XFQifSNtkxn66K8cFSbiyvWnEE_HkreDkClRSLepl6xBORP1SO0pUI_zRd3oWT-G0UFkpPkhqcg73K5VRdRL4HyDXRKkiQG7WTlHRLaTzSG8-bZnutVPh6RUWWHSPKzfJ4-zP7fnq39AGrNJ_O0k5m9IBXd8weUZklcNv09gpXp477YMUANZ4nUCF3FhRc9CfgTdvS5_ILAh-N1OYgpih6zY1-3vW57a4DusulsjbBCGCra98ICEU4YRoRCKO1gKEDEPnMxfP-_____wHoAeq22Oj9_____wHoAaXtmaj7_____wE4azhLEvxAziRL1vI0mRObervq2sFrHLJvuAMCP1nNKNRkUYXTs2fV29X250kN5RZ34XH278-EcbWWffePWo5qzem49uf9X6tFkSvGCglMHW49DAkcNPCIy_HQLqocRKxlV1s46flkV5j4eLy0VaNhg8MvVCBsIfvDV6CCb9Yq1n2J9FyxHPi7FRcYE_pM8gtSzMUwjwPwZTkqIHNoVkXyBShyIqa8XpMz3aMG5_NjYFjZKB5Bi-bCU4ru0BpAL_XzVhUA6SX_UzgVN8aW1s5WCCFg1wpx08JTKvPa6QpdZxnOQbKPKKl09NS30ln1YGyFGsSlWv_rGca0PFJjCtUrOC0izSPoeyfQSeEwYPM3i9Q0wiLcYmQdP9ptX5SNCd1HpbLXOyHDu9_m3l7uhELS5UrwIs_RlhIIOAwRqZ-l8yfnaCFLIeodjfln2JlOo9iDLRp9BV5ME1FSIsf-nLt1QkJsIggUCUIZMoGc-OO9f7OxTQcjKM76-LnVtRM4WTrU0yt5njPCEy7iaF9jVOkMH2_HvUdgrHg7ks_DDMrnW_LRKWeRGz8L7G8kAgk39cW4N4zRE9Aqg7ajcHHE3-_iUWIPnK-2Xghb28ODjXbFK4IS1CfL3zmITl8AJ8d9awhTaGFilMp97D3edjybAtDcjDvdRkLgSCdRpNf3IK6Q8ojnpsl9Gjhr8F-YdjljSfK7UU5jRRTP0qjTOBJBiAx71hT35U_bPcgFPKjEBtnbo9Z6UI2CqEls5wobnBYIVhVsS6nPmyfPv0WE7PYV-bc979h0n6WAgTo_SY4S13yWfCUEIK4lJqG3toEes53g898vgYBJKgESAmStGpsY5r_v1HCAmihHpOT-kDAYDTzOTtXnGESMIabu9Jz7Z0IeudvLnDMIvld97ZNKzDvOAsqdhAFzdTDWQ9Vt4T94ros0ufiOTn3fAFTgWZ57GvlDRUXMpfoQkAl8fOc7B0szSbhT52d_kCGZ-OsbWLKQWnzaZteV8Qm65iSuVGvPUuebh6wA4gCx5RgqCeD4mmHRw2esBmoXtPPN_p-0pkVE6mwi-V6rfX2O-MYIGWKyxN_Q1zDAoaeFWdmydJqMyspIuNADuegX5uviS4UC_p65dRuGr28YhZO8SUTIefGWwsIBpnFuPfZkguGjZEeMxdQ2hCkKBERgYMk8VjFCIh7oSJrwkcH8URG77GwXN9fKCZO_emc2yKY8OfBfHhoNy_lW4M2c8Oqb9V3Fy5_r8lxVQ0wxazda0S_YFdPPXlonPj046iQKY7LzhpjWTJkOAsFYolMdpR4gw-0lIEzKZEG3Sc6r0b12ioPU6X6sYXoyGy_UZ61z9OsarL2kXF-u822HJvXQOSFUpicmFpPoGM_23kNUB3vHdM2Oi81YreizHR8AD2BewobjbO15dSKsFpKDqq7WyMmgMx-SSgW8cpPcG2eWYhyKiCqt6UojidpQZAvBv1DUdOC8VK3d9IVy96N_1FiMy45CLXCl2i7S6MY19EwLjFuY0QBYm7gGdKeunnPhfyqi9FvwDOukbkXdYHQn2pc3NYKbnxtQuvEOryGKNAXQuhSqOCZkf5azWWtV7LKJa-6LCtqu95rnVFKj1zY7W29yQ_xz7VPsNYIZKAiq-UKRZqvyKAn6P_qWr_-r00CizaMDH4FLe6SVEGQs7pak7gCclNRGqCA99Tfq-ARnyKpPnR3GR9CRCcJt2kD6WPDVdW0dTBumSgUrGlFt6AwXpFLBVdJxyq9PgEmGxNgs_mkeQlRVxb7tcmKZZy3Dv6v1dB5uOl-hUvj3rajHFIxOr7xO6KPYybgdMpGjeRwmbLhbk5bHUg5zW4ZPwM7Xo9QsqXa97Zvxd7IXB4XbWpq-fULJnk-UUokP_VkE4Oy0w_-xfNNPhBmPz5Y-avGlWkuO12g8FNd84DNCqVjCcmNAye5U5EX3iV-Uzb1fVUoryIw3qR3ezklCwM0FM1dTAU5EtpVkFPlqWiMQo6GS91D01dXJZK0RqhmPF3JJA3c8Nb-cOok-fOXlbnUwF8aF98z4nEWyUwpYVgf9_KhHphttWuW7bGQywz2KwYducFArbY1iwH1qke94lUMkrnFTXma241XVdOjL_mlYmuwSO8AJXPMNWEEXStmPO70gTm1VWe7DrEwRMEXu4GRzidmCWCHq_nusR0IRz2D7OQzpkP6tgp3SBX5FeLMFwLD0MWCkpw08rQk1eZnzey2travDHew8Vt8iFNGMklfvhNNfCYXnc-Xpm5FGBT2f64ILKH5R04vyltA9bxvtnBHp0-5PoY9AN3qwnc7kKTFIzlHVart6LOVXqgKlmQJDA-o9W1c5K95jeilZi-c2VTHZ3bm1MlJIFnvQkwm9LOcEfslrmJJqn2DRCqeGt3IayRFRAx6s55V5bYAqKzuDuZ2ZTWU1Y9InB1M7oC9qZg4tbES38pdVLG3S5wmCJwA40IBQLmX-UMzK_DxLBoZvW0-a5oK4vnFFOj1YfM4S48PWK9jcH9J3FQAHshOYPDOyiNUByr6j4baOb7sANa8C6S6PJQRPwZ6aFlIicp26xaDfM2vqAti5qRLrY7KSb5cXwSukiPli1S9DxSrSd3mLvP897sWB961TRKRbDcZ_XcHxIm5bFYzrkpFWWYBStsE9IJFrBfF6b-0nNSvH-wiZ53ehV4_uBZRJB0q2uvCtQpUgadip1nZNbmxhWlj9egXsg9lYiLfb6q6RXs5CPCkOh27GCBuCwpI4XuzsV6vyykZ4KlLG6m8x3MEQKt2FUsxMBiIsP22pC5qjrS9G8X51qr2oJyEJsI2oemQBSvBuvVjRbazpkslUnNR8XQ2MPJj8_3bddCPichZwxb2VQSoYbJQtSuhhz61JjnPQvfP9RkewQlk7yDA3U85z4vU2ga4HvLOpF1v2uVHznpjJbxkOu1zDyEahbL4fL7OM9J5kDh-fpwSqXjEnAY27gDiigMeu8RKPG1-AsV0Uk9PWTzNDQI71UO5CJ8SnVzKCAcP39tFbPtPN72c7aM8KM49qyAE73d60TNX4RLGeSYz5X1mm6D9booCVypZ9qTT0yJM2iS3oMF6vXLZFcwGcmVy8DUHisn2OFD4DmHXVq9xY5jD3gUQSupt6r-AqXRnnz1rHLO0NuaTvjTp3ToPGQj0xJu_WUiXXj97EYWvPNK0xIA9igsWcrdMBaPWReabjNSdkzvJQC7nz1mqXwKLnDfSpRRqkfhs-y1cDYJ-oDYZLOpxMn9yTCo2ZSYeqwwiQ59QXg5Pm2TvcC3lHN4oTJGCcoY0rZOh0wDPCxFuWurqHaGlsKS6vCIFoYqozwwBR_rSV7I7NuFqAVddGb44mrp763aoKJSD8IaiUFLL7KCwm7ltgh-ppnWUblzUtHTH7QZ-GEVZEM1L-m2kAnMTcrdcEc714znZozUxQQsK2d2_Vz1lESaTWIatRr4036DDdsNRn5uXAs9T9OTnLJJyjXfJO9yHCmUKsofnpGzVx1txWRHySZ-MD_dqUCP9ajuM5VuRsc_Al25GLaXBvUedDQiGUZKbjRskqqzPBX1IU13bjR231rYhh1fUtHvdbj-kf9Zn82aqUxEq5n4MQGJHStlNMCQzTW5M2mZzibsHKeZzg6_f0ia3uzZhiYYebmryIoW5P4EV4o_XpGf2aTLJNmAxwdqvV-57zs_NvzyfAe5_0OlLEg0nf60zbFnFlW4SaHCfB-iNxUQiDZ1Kmto4wMZBxXDfG-7DZr7Wk3GgEBipQwdz4kewzxtp60pQ9o9MC--iniLl6KZ792bzYINf2DYc5C3dPGF4otnU5Kuo_b5710w8AgAPL8h1zahTnfPdOemVBvygcomRYwBR1Q_UeF1PgPmzphWrSZso7VQpgCtQT9sObNaQ6Ra5D2YGGjc6gttVfIWn7xdqshSqoltjxFXY0D5avfWMVRLuYPQuq8nIYw7tu7zE_YERYmSJlzOM9WacAXUANuqGWEzzmzFaba2vuWkRstRHWdeFobK0EPslAKhRAFcd8G2RwKWvWTlp7fYFjmi2SIOfbxZQaFvb6DANGoAYWDSBneVupSO5St5KWD0_JNVl4NsWPvo5UG0U-Ftooi3VSjWqImT0nWx393ciBvmU7H4Qsew5zYYMOgsxKchl45jOKU42KNah8PijL_4OV3fZIHhfo11VBaisRcGn3RHHTduOIMpVXtWu3RKwM3y3Wli8hkYlGJ_2O58fABWtbAVfGpyrA44U03AHgG1uwGP3YO2j9dkwjGSeufluWlLCv6uUl7LGokn6DB6R69kb3zI_IzcDguPTmk5r96yEPq_sFCOV9nwY7GkxvIIxqTQnuh82g5sMsf-VqMJ2m3yvX8TT6aaRRmjMLnm-5kltSQNTJ6q1nAaCoBa9TLdFE4oXEK2oCqHSMh3QQqxPqPc68_Ez2FUtQMRm-1AXtz5okIcww192FMsb8kmPouQL2fyHTvLdpfUzEFGpSVVYjKZYsc-v4ck5_Y2YyxhLHa3_jIHWY4PCjq0T_iu35JRYuZPrZHQTqQuD-2ZUEp7XUoh8U78E7k51lD4oI-BI8a7UbSocr7QnuvwqXoTZ1qGGeY3VzIkaj88mojVZLR9_zv_Q9QlVctzwUy53XSeRABhsOoKLTiQM2kQmIEEyII9veDCzaMWuI8mdzsCeIZFQmME2m1jPiredRFEWMu2DQikggZRp90DgRLO58W_Iih0E2Jw7i6MBaDqEPXs5mhJWyTUxwXjw8SfV4kBNao-mXnrMJALK_cI9pdTLxD4o-3xycWf_H3_4kM64dXyf-v6TcU06LQ3MppAGNEeejLEekX0Vqb_aEDlDWMBTEUs4v9PzbLER2hAZaI8Kn_zGE7LV_fhndAsFhmaEHwf-q-7kF0mqSgs4kpwNSaKSL_ZHB9rM_h2nkSWojqWnJ-s6XMEF-d5SP_jDHY-rUSvxcBb-24zN-u3fGfwCovTwg27H9X_TedaaGaREU7dC98pnYCqCgaR9_i-sX7iOJc1pGXYJnbTQ8S7oalzw3JIUdapFYFlAxg_joLoSAihKRr5VUIcA9jUu_qf-Y_z8AtPuC8jhJH3gvOafoQA1TjsmvloWoBPPk4jaSAYQH0Ke1NfbGo2GHV0Gjbcyk3zh6dCeVUpMuYBuYCl5noyYWmDvHrM3oRKo_CYMwcy7SZtM0MhipotFLnqPzyBDxoOWyWSCDrA-_dAnyI_E1ebxUSEiUV6-DBConYL2XQYI5Q5xVyLUpERADeuOGTlX24jMcE4zWINh4hHK-fFzuGkXg1XRsDo4EAtGe84p2QMzmyofRswUmEHl0P_-_CIyfQboY4Pl6Y5HFZFxP4BlNvhbMD3MQbLJdeUgPTwKz29mXkf0YGKqFOB3vE2rBnvr5NPA9ijdUPKaZaWnlJsBwFp3qxrmi7iZbHGkkOnM-f7W0hbu5lo8itHnr1Wx3TbQ3PNo09AmSZN48foYodGJrpztln9DOFX4rWBZdQ9NwVTTVO4uXOSvV_P8hB4n-lfFYnaMysUXerZlG_k_1vvITY18HkvXawLDKu1rOUYOEEkvh6y726U922Z6M_0SqzCdtA_o5Xo4zK5PwOvGLh5fK97YChr7s2yLpr5n_FKwdXE5ld8EbklwWtJzLJWT9KhECt6YO6KOr5-iiUEbd02Po5UmMsZtrkAKr1qrzPUA5JHNYkjaEpCaAvrYGfYefrc9KmI2U6URbkRCIGv51uE7SmZNjO3xtqgnjQpb1vuXy6WR1cOk-RX1Zr_bQRQOz3-IP_7o980VgzoCmhLGM_Z26f9P8zgNnc-N5cfHsjImx0idj5dgwW0ubu8ySzQeSUt-fmN8Q2pstxFL8OIoKGNcrgjvHLycnekqB61i8OLN8HxynmwB9iW8o29JfbeRzmfaBKx-t7aDXCJXTazYzwUqb2elat24Iz2YzVAkksX_q1zqR_jw6aYhuPQLmqWTGSB56Gbsrqc78wwu0AK_VYrKuItfDXC5BkS1veUrbKg3Hq4EuuHsVDr_ETikHTrlSmhNuBMAD7YjTRGUfdQE8ZuUW93-Mfk5d3rfu9gw0OfhZ8-xNW8mmgyOAFEFC5Z03lQLeIyBxSfJC6BspOWM7qeABGyHW-Qv-ZApgeubQLdAQ2Xo9mZvu3m-wmWN2pims5WF6inKxwElFgSw194DoiZxe_fyIU75YNnEYQVe0GRe46p_wcSJgVn-s2IOngp7DTbhbuWaIM6eeAcKSmxv-pcvn4HF90FWir3jJ-XPqB1DVb0V88xedUMXUxBQcll-NODanY2TV_twTCs-FVZwyMLvwziFcQf_NTeuiPWcrO8roEBU9QQXtbFTohf09LjU3SJyczE3Ch7TSPdf-jrdmm11LOkByK_djMbJ1guRU8f2EVSdqCBs3I9wvpKTTP7WX-Sc0gG9CzeqOmGhD_-zdrn42sCgIDl_MQAokJUFvzebwyXP9UrvEa_v5GzgLsd_Q046vB3uQ28SiEPOJ02bQE_Mtt1ubH7ZvmbXCOf5cRa1EOczTQOviKxnWpjRKY4Qe6W36JTo-V0hHD4-618D3WJ4iybQvNfAsUVuR72YsVeWMXDrHSJwFeP1lkILB6HTjMDTc2W6K1CnygwQoEOPQq4DtdZymzQWBhBDP1Ywci2k6e6OBDWQAR2SgBYqHHnWx8Pc-VvdFKpd0LMroR-8reaHIYlXgp2KWdufQpje_p3AhCZcMASgkUS-L8jtPVF8qFeQnwXx4mtFl8DwUHLU2QdJtz614THSK8HcAG0_lCnXBgX6SjRYlhp-IM5WoxnLychOtvrY0zae1F9bovEFje74JERHqI2ucbrsVP24u0F4OyqBWpLuzlUlWYqK7BsG7I3lLPjHJjqVmcsetzR9IDXRFM-Brz8RBs68kylalRi5-69ywa9LLX0_48AqHsKNG6QWxUHruXH3dAOu2JQANtdLU-BIiLxfVed6bObhfMrf08L_AhPm7cJa84NSI-yuCo5BN5CO0OwgaqIEa6dxaHhF7M9Bdc_xxcseh_rXmCBnd8QzgUeEUWVUuHJh0MXwckms3mvLMfESt6pxECr_naZeyZXv273BGTMiezp1FHNW9L56RlZOufTAyXyyn0C2w-WCuQskxiysZyXugvp52Ph4muJVxjQLwrUdni1Je1S5GchxDewXDJA0uXyx0qOcFXwbQJ3rhrxrr9SbtfX1uFpqPw8C8UngMlrFl0V2eQgJz6TAxfGcxfz5BcD2F9WF6coLBGsOCYmYAIPoXmMU6hNREkRBxy_Oncy9FU_8Z6j2elYV6IOWVDXXQEcMTQugH3LzBIGje3o4zlB3ZewszvAqEPMC9j60QImkGSp-LRWBS1Nd8ZpJA0ikmOiMdP5JXjPID-C8qXhtVgH8ILv2IXVoQY_c5WOig2g0Yq-RAlS3bu1gk0KZ7XE-X_aWzr5AMxWcHRfp_WJVn_0TR_EnRuWzapjczDX1AzCvfc_HRr8qIQOiIhD-HheQCYMN_F_ktCpkcBcK9TgtDeQTTmqPxQ7oCngzDdKdZaDBsZ4JHtVALAiE2QDRlc9G1zGTfKmGa-6m2YgTxFmTE4Dds0YWmcw1d8hD4D3LgNF90noOtQPwLqijzONEkLJtcqpVRqdeNwmNzGQ3duMvsN8IyVgj-JQkDt60JYscShJGks2p3G7zQ6nMnE42mWEceYjo-LGeCb6Lxtg0p30vCOjHsP5-Jn9c6b03r5f0OMaVKaw980FdfC0WoRRCkzzV-EptnPgKn_pRiMn8_x0L3-ezGfNtM6F--voSmgqTEVTcovc58q7eJlvC4Ih1IE51bUfMr7i_sMH6zwX8g-Uo_lkOcKBzGZNquFmaDVhlTFZGST5_pWUVUlYFeUq1QQ-fN8bJ0D-mRtgG8j9a4i5wRzTRtGo2D0a07zKAzLvEjxC82-3gky18aD1rujWsy2oow9wIZKdJotk1pX4slxv6MVgn5zActVnc0Ar6CWOGthbdQhYELGeAJLRseEStdm4tn1u5bLFFpr0JAEt4nGADaS0fEUdHKGqysYiaZ8MNoZMYQ7NR1z1nyVEo0LYg--RlpjlM9gnYQOQv1TacT0AMDZsWcAy2GpePC1_ApygvqKGEW-j_5YHQrsC7sZsBOPrMRTt8pOPsl2xyJexUct2HQ_PWcRtsMACLFnnz46xdgi-_oxmJEYgKEuZDU-Sj2xLZAIuyFQ20bG9UvwW9LlleDZardtZiGR4rag-igF1yuWW7KWl_i7AoUWigR5oU9ETUM0Xpc5pU3To_WFQhKlQ-3zQ-LayuGR8faepsgfbJcKYO-642Iu0sCtckxhCp1olI376WzU_Hf7gns7it_cT3CfvKKNLnD1gxlIAhgseQ9GYsrzLUTRzWG37XDfr1lRKLsf8mr_bn875KFM53XHe6IziB_r0ZXsMXriUcku29rIkEdK5Pp2xgxPIeVDdqzhE7L0-8oE6rt0WgaYi0jAulWJUCYVWVvC9FYGodfiPrIf65cAOpjWdDp-4N7qMhBUHEUdGhWvBsHL5z6JvXpZk7z48kHm1dG243hJUwgSPd8dG_LB1_U8NpvUdB2chGuvAXTwizk90-skMUx_IGfTlSMhwoeupBx0L6I_8cxNkmSy0MZyFp3Fa93a38YEjyJ6cXOxQGbaBxUsZkp29XKjE_2h90f0XuNu8Q6XdxzHQiiBSvevVRvgQ-rtGS-Rh9HpSkltTo2QO0_Msl5-oIiIYnfHC-mJDyzJASnnMVquHyfCTcfPWpGC9BBU8DE8AhDpGXtn5uISjG924yGEd5GZqFZWjToQ6HGRrFb--CEdcBPIZxeK-P2BdDBNdmSEw74hXEKjxuKQPPFt2bWxaKARHbrjw0vESOE6v3CR8SeEig5plMYK66AuAACMu68lrJt8t057ju4SYf8d_M-SBBxNNSWegDIERUNGsJ943CkTGjdxmlHmoK7G4kt9SgX0fe0_v2irIpX9JZ0fbdKmQYR01kuzQ2W8p0llxhYspc9r-qQTnpHO-S_HDtUnYqPiEl5nvYeGHEcFhdflcIK86l8C3WoqifaW2rjc-RUHCKeu2mkOICUDq442qQtNMzCdQW37We-74n8l33ly_ovOQ8-RthvpDTkUw86dlSZJN7D3FpYw_S-EwKaXEEMet7dZoL1KpjU6lo7lmUBnFM_xzewxI2INdPtR5Nv8x3bBdvr93m0er8OZ7e84Eo_ufqAsSKh93vjZcVpQAunCWfHZn8HpMF_SJfWgl_-JYw-HZ4y1UMb3lXsnxLX4OSP1kbAQHAX7UQMqss2UieJJ-SUH6n8yOazLGmesNWIVMjQEvjN7Z2OGSPN0-sFMQmGQeh1r0Wb74Xpl0wrOQNLl81TtcAvnZbN7J5AoMV9HiRCJngvQZ8Ku1dE6QOfofHPVxAoghvbsHUAdnt7LNkQ9sKZEbCfgAmZEeQTBLkDwLDSMVKwzye0uVguhfPJ0CaDekGjrD5L-F-8GfdbV5QGfAiDlashk8ySIFOy1aKZdX4yCM_H2hsvueV70rrv-dmVdoJKivCfKYODSqHJFeN16yGt8KbrWsZf_2rM3HUFeCb-K6VxnRugMFIVzrGvOrYPioZbVx6gW1qV_kFGQQ83jsZSML0z9qgExqNtLbDEwTkef7YB_0V3ajbg_BhvCQxmKnmElbI4W1H_8yigjFsDK1lpwhIRkxqhMST9e2O-0gTvUN4SZXbxN1f98RyaejD8ynPIS71mcUe4nMSCx-lYW9HcTA_t6Qyk3fROazI9Evl5KaCoTTXObrIC5D3C6LIYt1PZ7q6NKOvfB-Fbw2fQ8AlH7JRWHVmeDK1LVD4EvlQK0-ytsgdE7bB271BFwvIdPabsyXQf02uOuxectqyvPD7RmEpSBnn5jRIsJEjXIrfpALVL6kNpnCDT471jLxcxdBprmcV-ivsi8TITQloDlerNfxxeQ3_KxoQwLNiyKPa9Yt4a7qwCkI195QCCxoLd8Pt8ceDNkjHo8pdUkPf-KWBl9RX95krLkPWD1DpzclK5fF8aB9dURp-mY_idJfKUm7hly-ku46o37XbGz-ls3xg2qUE0-SnZK5nD89gSA7Ud7bWgrvXYVgBkw9VEwLQZFvpFrlpaThru9BequFMtTrA7EHtOVktUIJFYaCmmzkV9baSt36xNYJrP70tafkBsvYYLtCNl7DHiI0banibLOFKYlXQ9KTENnBT5nzpmeKIbP_h90yOHRZT_Lk5FkwJfeVT1Ts0Usm76agiXfh4tn3MeOuaCC5PDh_waj-SkQyI7BgLlKGwA05hLNBHhXntdLNhLdNbub8Y_tAQoQWQ7p9kzmuzuLsJTET9ORZP2mccWeRjQaizgrcCuNPg484zzm_1xHeWu0mPmYWHWPF3h9_gJM0t7Z8Kn9xkD1NIFJ6lYTLapfyQXgJSBKb3lGxftZWHKJtOlpCkIdIyIpiGuDei3P85lMc6f3a5u0CbQ7S5--xchHAv1sxkZU8WmH1gHcohObCwTzB17FBPSAHKx6yUORgzE46R-ch1fke4N99SLmx5wqBgoKPxKEQD7R0OBnL6Z6KFtsQPGs8fKW1QZYI7FBKR92Md4klMX2oyhR7sX2FFw6VhytCuZioWCesRlozaGgZgXk25V47A8J6yQElIb0suJTRRa9oCG6iVUtVoZE4bG5eej8ejOCbAne5VPijkaz_9Vt9p1_9QyVAJoITH4fQJ4YkMYQcW2fk79v3RacUrbAlUvQ27nQuSEMmT8JxIsdLrdvAclwzPG-ZgsqlwIoKLgjO8xABYbxaWHoUGc0LfpitiI9ZyPfVQgcLOmmxd7PELttj-zC8sTpt6V9rzkAugV4BG-OUwhqvQGGKdtPjlXLJZQZFSsfkk8JBvCc5qEGe2lnHKM-HERZp_orPP4SXSDXJpbHX4Ry2IaI6HaTr-RBzrR03NrHSJodmDF0JaCbM7MO3KH2a7Zd0Oz-OgoFdyhFTM1muew0J0v43eXDMJt30wffHHJ7lKgsP8TOaNH6H9YRWBoDXbcyMXN-1tqjYHRcXGCmncqkEBpxqmkNMEY_alzes3y0xYCB5OwSEwrDE_n8rfe59IrEtmutD8CEU3gSg86YKK3_XB4';
} else {
    $str24 = false;
}

$params = new CommonParams();

if (isset($gmsLoadFilePath)) {
    echo "Using old checkin auth data from: $gmsLoadFilePath\n\n";
    $authData = unserialize(file_get_contents($gmsLoadFilePath));
} else if(isset($gmsId)){
    echo "Using specified auth data\n\n";
    $authData = ['id' => $gmsId, 'token' => $gmsToken];
} else {
    echo "Getting new android checkin auth data...\n\n";
    $protoHelper = new SmallProtobufHelper();
    $checkin = new AndroidCheckin($params, $protoHelper, $str24);
    $authData = $checkin->doCheckin();
    if($useMicroGCheckin){
        echo "Using microG checkin\n\n";
        $mtalkClient = new MTalkClient($authData, $protoHelper);
        $mtalkClient->sendRequest();
        unset($authData['idsStr']);
    }
}

if(isset($gmsSaveFilePath)){
    echo "Saving auth data to $gmsSaveFilePath\n\n";
    file_put_contents($gmsSaveFilePath, serialize($authData));
}

$receiver = new TokenReceiver($login, $pass, $authData, $params);
echo "Receiving token...\n";
echo "Token: {$receiver->getToken()}\n";
