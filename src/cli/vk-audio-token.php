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

    $str24 = (isset($droidGuardStringFilePath)) ? file_get_contents($droidGuardStringFilePath) : 'CgbC-r9OsTzaB7kDAOuhCBLyM6BOvSP-vbtLANoV6FJa8qAPyyWnIM0aO1_guBvVA5dWr7aUrqtt6-2XKaWJt3vAEA3qCrMUMmSN4kdWWrA6PldlN0BJolfQuHJ2jIqD-gcKsw1OwmmCbi3kgi6FD5pV28jYIibkVL1HIC9AneVx1Z2EaPTLCS5sOy9dByVKXggmXn6oXeQlefiQjNomuGmdouNRY1inOlRLAa0tvAD58DCbeHxBmbVx-vRna_2Gv9upxeDhDz23XvzJhjf6PyLt49id13RZm7Sd4xNZc-fI3k2hSIW-u7IlJc-qVng3fL72EsHgSeni5StSpWg7jg1Dl6lJCrZdyOxywCb7whDhCi0yTq5rjLLK6KVaqhsI0u2-KlHt3Kv-H0k7SARSq-G6qrXEh5vSA1xaDfKDQ2o3S9adFUH5Hg8vhseYyK77ZQymTlPCz8zl_skdZn3pcXWMfp4H9-w-1B4QxWDluW8yazZPDPbfXRSqO-DYSZ-ZxLre71hYWrp-DEkCTr-UDafLYbVPqtRVhEJ1FuX-4cO6OxpwaGbBF1ZrsFWGhEIeEkA3u9fv8XoJMoFFRS2ISOB_o5qnOGEaDAjb_YX_BRDjlunGB-gB6dWx-_r_____AegB3vGZwQI4azhLEqQ_22tkk57EEcp4JhiF_qRPQR4ra7NKxe6ALEs-9QMNqa74VCMXCtbu7RXS234YJoX5S4V-Wlx1qGmkPP8dOm225mELt9plHw00PEGPMwJQ2_hY0nI7GqyEgRLzwuGhFe1T7J_qtVCKzkR3eRNhU3p8XEa46oy0IXJK9po2Vqg_i9GM7EuZOLV_Gop77Ify9VOeCH9rHVyk7FR7DYpoa1bSbTTeZuEHo2wh3JDvt95r7l-mG7XPUK3GupSpDKoFKNoY9xjrpjv2FDJcjvfVtptzCUs3TU9E25t6mO0a2EPG9k0j4mIWK8li1mzYJIcd3Bj4obMN2jIyELSvYFREPcyNjO8xK_7HX0Wt6eeacg3rzlFOHePjsMHwQN1u8zJSd4xV47omhy3mv_AJEJG91MpVySNAJ2w_R4H660IXWt1pe0Dl8kdEA-8mjBdy-GaijIKU8c67IUQrTIHo5QvrZEcI_cGI5Xyoo1N3-g_AoKG5l7Wnc6hyCbK5rz1bHE2MgXJGDYUjgZNG6lDAXUvoj1hHYal-tVSaL6pvOpigClzFS9Tbt9q_URP-f-t4jbI6O89ezFtOkeXpUD_zxwqazoqZaZTI0IEoiv0d9s1KnZ510PJ5TXOMiOiemuc1qV0XeH5KGtwwyI66o0yLG5I_8qQzGuiJBv2lPa-GkI3_0efx1XKIT9ItzMBcPoWkf8FL1EvwA-dzpFy969SPPKk5Ubt1Bo6kkbzLLTsHmeHGVPWGfIBVNxuE5XMDdc3NuFnbysdfkixGFUnWYAl9p4xgDUxE4XPEC9WYds66KmSjoqm7JlgBL7R_tYcE8g_7V1d2e4urccUJeC8UBhqMQAcU2Idfhzd9cZTjjMKotc9cudd8zWB3kmGeARm7wE_fs8F1gZXBrefc9ZCVHBkD5ttp3LS519jGuN1uH8CyNkcPOLhelLIT_fsmBWXAWa-c1LxCWB-QVJfGZhcESdxqw8ZIWTo_WM6pjn7rjyfEe7ukdaUE6gvEXRmhSHYrKEGNFfImjQW7QS9AKc6hRi-ZTH3eQ_V_NVahA-PVYp6n7k3iqicSCCo8rviQsA-VnZegzkWkVjqxts-N77qsnUHmjfxd9wflNxVrMi4ffADJHc7DPboeDTFegJlKiGcNEQbuuvbFBWsI1IFwO0y4CHCm-vTmwetPrAKhkIjFZGGRRt6X8686cHkWo9yU4PQ_WY4-IgM1SC_JFATtr2q23auVHFUpCB8ODphBgrCQYAIRIdxR6TQhpGJE0UUouP-kTBOEVnHjPpcCSR6EDLqms_4DDzgSJw9cS7x6i73jijvmObjHh08JV4k52vxprXA_2OJbp8aetqTrHOsyJcRDQ0nI9DB_apk2G9tkqp6M_lS-x0IuYbhRisQKO3Jtz6p5tb3CEsjtapBXmctgo7mM2HvKRJmsj_6xnTZT_TXAsFrHK65-cixhFwE4vr79sRt7HUYDBiXehJDHoaSVBEQ30GUfAbxSSDH2H4fJswOCJl2A6MZ3vCdyspI2mWVr5HnJCWVZDqhQPZVVDEDQmCfhE3AAZzEaDoAzs4v9WKSfOWQr7KVebwRghmFTCnZifiKPqtxHJd1YyS0QRkNRLkbmYIPG8BieTxoidHO3X_jl4s1_fuqRWjQWE6uYEfKwlcR9SxMfrNDiLVtiKvkGM0MdrPDrrsk1xRWYe5_Jh0AyZOkfjlC_HgEtwuhlkGcGAux1e3kSBOqkfJLZiHkKiA42iSnZi68N9KRMhbmmG6jIc_rAiT_KQIPisFO1RbWDGy09yWAwTqLOzkmFaxNgX3WZJZwX0oDRYC4Ooczfp-wo3mp6jlyZklEMKenRYdNDThSQROiVSLNaYYfxsEl3ZAyJT0HZbDyuJRcoOTSiOEWLgqIQnkF2ww1xj9oIW-49vGnp_oU6uVHSP-gVym2MHNhowivrDiKkGQcBC5bAq9zbaVvbS5zTjAgQRlfJN4TLaXaLD2GqhrDyfFEl8yGnaGnkS5mzHwumrdOzqDsaAx8I-2DhaUbWAfk3K1INGmEuxdhh0z-SjhCJOcyLHf0bJ32QzqAjWiafc_BgbpqZokgGrz04nZYrf1MfeO6TsxTm2KkQhdBchwMNYFPDsZt-y8wYANAQhrNW-R9Skq5QlqnxLMtAKO0xPafa5hWBRve7n8N3lg0n_d-tqW2xIE6mbn5kmmJgFZjaK8uhDodWYQAW7Nnvh-YR8bLHiZ9uHX6EqxtXq9DRLwXVGtxvUfHX3jMNQ8ERMc_98I-hS0lU166Q249IT64gF94cTu2nZWMcO5G1XlfoauYp-OVbqtYmt28_1o33uAo-bPkEFIvDRdsSzxP5-3Mqsq_8bh1kMIxGAw_0f8CvxQ3p8TvMaWkJ90itnZexd1d0U_UkRbSX3whu2-D0e4vY4rJpcrgOAyd4R8lDpJsyqsOchqrX7TcGgeMsyRDbBoEESfTRCbEkb95m_Z61tojO1oUOv3nn1ryXtyRLPeZrXuc3MimUbNaH8mlCh961CroIS-uS-AAp3nd6RHiSr15aZCDMXvtNQej2qhtFeSCxDdKI5PDkB85JbmKyyWElkj5cJbCxA9YLNt8oGYFUFuGih7bU4pz2PWlXwRbW1Y6Y7RuH9osjzQvVsffIYIRimNb9bvmX3t1OuHG45Kr6H_x6bZ3rX8n1AgJt3qnA1WAPaG_k_QzNyILOFnFJ-mZQFYTft8_2xGJOjR_WrLUvq6IrPNNxHmsDwjHrPqu1acSGaZYSSUDyxZGQbZUlv0EHlmtAezwlMQlgoNP2ji7ig3o7NNYjf5pEY4M5KefPEqxshRA2CfIkZJqEFoykHlzY7-8Lwg3Yrd58Di9BzvzgJVm3i16mQ-YwVj2VYyhmnsrAXzUX4mC3MVXsf1O4j7HANcYBS96M4-sB2_W0N1qCatNMK-AMpNGn3fnXJN3KoRJvWcSL_54j0GU8akky6G6pl0a_LQM1s1edOcKkr_2o4YoOK803nwfGwv5UN9Oq5xHIZZzq3UcewXYHghYVLqYQYsyRBwzNycwPz4aAcmEpat6clxSR-H5PN_Tgkvc86vV2yAHC-9MquXYtG21ZSVQFg3OlK-jpNAD1mzdwGLOSL6KWAt-gqDhnjURvaUpGMTbX-DruXglFdJma_SntniSCWzFhJa75GZjHEBcXe5Rzns87RCOzVyu8rTfbxu-HhGok49-1dn_AGGwUYDU1vEQt27VsCetOYTujuUUH9DXONhCSirXbftR3uZ57ZHeQ5ls49-GhxgRp66q3yaqMrPce_jXdZmSA2cEuvYaSRWXrK8u3wAS-3V_7y0LzKvy_t62lB_s4n6-2ywlp_qHo91OfwDWSNM8JbnBIU_ao50KrIHzNOASQJL3SIwIgwrXqXEq58-5DrDxyNiypSW66GgearcY0S4qAue1Y81LgFe2-4SIeiVXGILkl0OlJq_E7LEjcXY3IlfjfBuvFJKpqCtRuVbvPreuKTKCvmW97ybhsxXGOlyeTeY4OReQWax8jx09fzGjRvK0I9fkJMNP0DoPcPjIxicVYKh3j6sy9qGFAdnJ19MuxODeH6IrpueLFbaggd7RbR3y1OwYZDnJ1F8O4bE-mSF5uqlOk9nXGAevxGdHkR5bPUbwMmg4eWq8i6q9apWD3oJefQg1Td0gIab5gL0HS-CSvNLWbfxgJ2w-OavzEozsAi6tkMDcfeKcErUFyigPV8yOO-g1j8sq0jOV6XOWqHtcDF5pHGArx4A01blK0ndsmmpFwbBhtwujRTCXfiQTmRAs2TmZCCvo-LzuvGD9pmUYGMg9QHeQC7YutIJ7AbEvceLLKng_7OabzROJ3z-yNar8osKzlAxAt-mOFrZ7diwPIN_iGeonr87bvzHdPsUGlw8TJQZPOIV32elicvKb9f_PE_XdqgLlZ5urua7ZdBP7kTK4oQPY8Y55GFW-v3dN4O7g98TrKPKjWI_W9AQ34JXJ4ulJdMgqz7YKft_OCHyc0vf796DAnY31e2-_CL3Le9xBxXBC1Q4xzaz-igvsAWbGL3eCJ8NZ6LXcFrRV6KAdjEUOfY_cp_b2Ghv1H6PijoVKCEWV_tw6Y2UJsxj6jmMW35n6WqMy_asVgYCJ6eLkstitlncZ9UvBplAQ6TlSIRslk4RXb-Cs8wzk-JJOrHOHyriueQ9PPLOuCiiNFvRU4XvWwZuhv-dE206vv9UAxiCVRZhX7AOlAktsshDIqH3PogMPPTXg_mfvhUFVP5yixFTP3eTcoLW48SENDuieH_s6BDzVv5U40p05ng0iDOHP4SvhGFl_J0AoJv8Dx8yhdQv6XfDptELOunXGN8UANjta-S9R2RGs2j2LLUxLsHCp5Aq22E2LFhikeTkE8ckxiW84NAtK2fCYZSyOWqX7ANDi0Ndq0GcOOkIkpsd6Y9_vtyVMuPwWC1lJlGhESsKr8U15zPoIazDHwPXkkP3i6cT23pIfuliTndzz7T31o-AxdD_1vJjpf1adqs8GoZZrhoq1NR62mNek40Skszm58Zi0Au1IQ__A3Qelv40bUFdnzP7Zi9JeVIJy4U-wPG3u2AXM51hyJVDOt2rYE0Tkn_VqyvFi-qMsCa1x6BmauxENxU0E119iw8R_bvjS0fwgngCONkOQE2acKP1aliLzppIU4cwiWBXC2uKtcgM8N01RRQ8s8ovIa92VMtwoylKbi90jkHiqJL4UYsgpgv-566Gj5EnFQ8Y1as7hSSwYrYz3DJgDPlz1p6crHMdOAmB666mcD3BJNm3TJBsjc6xBViQWvdnNNrR92zQryIrujp-jEIGpsTNQXMpb9PELonzzyLnNBHyXAdHeQzD0KluDxInQInA8StYnLTLdt0nKm8mOvqvR8z7ftXOPhmsx_IzBJuSzjQdKav0DgfmRRTxWvBm7n1ORHOmBipXEqmxELwesK3ValjP95h3yqpeaadHdY_KzIq8IeDO0s1nJKHFHm75oneMr5QDGwHDNxESi44upFMv_z6zRWsTkICFLmAcL4gFMZ3j8X5yY3cFD_yv6l0xbvyjLBGySbH7199BKlPB_SrXuDU1-InWKW3dAY8R0SOeRaIivbkPVQSnrs6FO8Atp6koVUaDEvSTz5ZiarnpJSpyszfCfCvU1q3SeDkq6vYtjRKnBTo-mQsmR9tv-50vxAaDDWY1XQ10l-pdWu4YTAvmd6rYHJHQ_fCsMmDWT6PiAU0YxsFbpb1rfaAPj-T93MpulIVbEEQIznO1ns8begjyCzbfa6eBMGMnl2lvMz_qbsJLKEdlbTOFu3wkPL7Iit9icnGF7mTcRGqs5fgCvw_Ggg-OKCGZSyNJwpNqto1iVYCcTUqgIM4YLEfBy5A_y1FN5wfLpYII5FaeXHcj9mYLjL8kPEcrRScuaJ4JOQvw99gSOh_n-AeIaiyfIZcC7rW2E4-gDq9idSg5aIxagJWW3kH0droQhOB3NhcEh8wegWiQtKPs9xtTx18kZv44gNVAHVw3yYeUW6tItyuCDct51GDF3Q9ow53oHDD4SWH5OQdT-vVDZvm_MK0KaR6QVXUQ33nLkAFtIygPsdRBZDewDxgTdZL7PArEd3TJFwxdtPa07_ymh_id6-Qe1hC_Th_mGxI6zVS24_Al-e3ApTHPYi8Glv8CevLDz079Hf-OTK4HTFH5hRe1bxEs9CoHnBztTKjshp8jPFuSXBTGU05cPdr80IsvyfYEo_uW8W2Duhd6pVjJ8RKvrbj8XMV2IwY2GLrux5kHDQvGYTKNBSm8WGAwIfaFEYU5HVeQT9vO4JEajN3vV38HKG48__4Gko1YRxjSUEZzx0dM82SB959mN5tBA4Pq_a9eynxWN19KINg-25TL4xNMqiqRAfrQGsjiTBC7UL-K2Gmj0MYSBTVtCNdrUwPGrTxKKHFTqaS5JQgzXLM2cvdDODMgrKG-IZ5MTrbmTduNYRcPPNoW04MGm6GDiRg_lK31bYeCa-2VJ2hmCnTWyyXqFsS0ALMj7uwvDj63fWDIdDhh1efHUNzt5HhYmJeBG0Tpwqy5RQPirBZM0mKyRDO4IfmVH8heisqWtx1QI4Eskb_Tp3KLuUG07OztoBYUDfltecfWniXx5LDX6qlvIi4KUM9vT1CFlsPUUnN3t2vPYfi_N_rmHEsyydLZThuuSurcxcynwCFTqAr5UKSMJG9bununGamIuIczFknjLCmMg9A2PgZsAehcv3DZpZoVErflKDF_D1qbGBGNJdz4W-TqhJShjHuSHaWdwUQzInAwfs634vNQYbMTJW-e72nQJTBFXoLs0Vm427aWbHU1GmUCt5m69AxdYTEObBB1T8yaeVBKA_Mnv7BdqK4VSnrklqLVQXbEguKnegNsR5FLGnESvuGCpnXL-mDIuQIEktrUkKxqueWbdT_-AREiiL1dwMSTtT-jw-0Mifoxw2X6T-frm6uZxRrpMLYFqclaO1MP4B6sESsFGkCygwzUj8wCXtdk4jZtvk9MQAyXI61vRMOuWqAILiN2p04pjE2xpbxt_uBTjZEEEW56wUQdhSrkPhGw7B6uIRUEcetzX9C9tz_z3iQfrFj4pnLylIGbSuRJQdnj4oeODzEcwLspApgVtAaZveIrtWoKa1N2lDAqO5C_tHXnGbJlrYC2SSD9LoS-Uz4hc_009JLJY4KZlfwQ07fdt5U02ofeboMOI8tNpg9erlUn-GaEJQTll4Konu_aS-C4MmJzxlWOsbef9Wr9V2MqNOKSjGcaEIarh86LJq_5EcGfhPqh-kbNynqiiTS6ZrF8Uq4OdvIULG7M4CKCWYSsBZjeQZICWKaraFSDv85wuX5_eogsIcuUFBS8jJXgR3Bst9fOUicu3kZ41rksXBjdqPkh1yy0XeJO-CU-FDwzjWmRDpaayLizzr9Vs4pAAJ9mOVHTEqzQMogZ-I6EZjgGbeSlC9WDcHBbpo7YwCuTz74KY7C_ZlgWU7Lp1Kn1P1ApPbRgS0vMiPBMwzgsWwOx6Pfo-Fe9JTZxGZx7sJxxcOACefgcJ2oJ5atJxDRYqZ8QYUZWyE6QKHyxs9Vj4HtP6gPy6V7_6nulK-JX0pp__qeqESGtscFYupxyzCBP_ygdLsmaeFlPFnKjLDl9psKf34KKl1GcRLW3Bk4LezAd5HWzQ4pfQNOFA-O7Q0aRJrgHM8QMbn5DMAAk-95JWNxj7ETHGY0Y4RXymicDHv-ikVtBst9A10_Ps33-N0rQ_L5Jvb43t5I34kpbWtD3YsSwfPfxxwZ-2uPQdCZMYxkk1-90gfGqkVkJ0ilRKL5nH7Dj45dwRA3F8wRsTVQ_HT2eYsc8D0FpoZJBsgol8okH02yYv0BoDaqCn6JuQ-hD0hdCy3Z_I7cMo_cLmSHGfRqbIQeKIbJIc9OLthW2UcTYrf-Y-_vYdnnSKOcI8ItgF-GIAyoBELAIXRrHLKR3QlabFGgEhC90MY83DIytcqlIG2HJ_mBIXiWhFQCJDSzNXxW9Td_OELJaaXGQJqP1YO_C89LYOAstsLOoYPDvpzdMFMPA-84cNVW5heIALHxAingTXU5K0L1EQPOBqkyFZSe1CdBHOgt1945d-3tFTKZacpKh1jb4Wh_QxcaneNOKCQCyapR_muVJjufJ5SECPwAwoRFvAaK5isR43_8OaS3947ZS2GOazgvxyECciWu-OvQPL1llSAGIrskaGLQab0KSR2dVvf5uHmgNon5XpTtW1o1JA_GfWvOCXfCmSP4IxAYNrNTtqAUPOOJMdu_iaqFg-VEwzcw3TCNW4xurydwQx3dCbxWjqM07tHmHMUijceh7cQdTZTc7diiBkETZNstlgJuFZmL1FVovMcSc4A0y1ogZNRB1ATjH3ONHkD93m_RtaDX115onkYz7vBfZVeOFjjDumwxaI9dGb0GbKsaqOnW9LVG1G0oiwfvkfeO0_c3TZvgYHjpw3fvkV-aL66uvYNjO6lF83-zPI2c69_x0tXxYRpKNMBUGPGLeRterrCTYYPLi8pTXiVDGEHKuAfiF6JN76BQkxO9PVki0BcJAmJctnmQMT21FU9uL_XQQ-rHmVHXeeSGJpLtnUywc6Knhw-fRHIwHGTFFZW1a5Z3ijS-k38AStydxOljMcP5xxXjbJXLbVHdrCaQPF5E-4swNOSh1SWuiSngpyUhFt3oXJxtfDzkp-8y0pimPWPJHeHXiTJdI6gZ6u9Yl2P7cA0KEmEDDcbj2ZtToSwrjxAaOeocZTpIxRJ_JU_N7Hhd7K4n6Dy_sxRKhMJROVuR9AVt_4MYU1OlhOzFfj6xq6sEUZ9J_B7grmjNjXge3MKECFeQbPUhgb6v4FntwdzMPuVqZmFUBLzYzU1RCoaOSXvTJ3yCC2_uTez_-uSKXYiFtZRnVZbJEk6GGVI60GT-SS-CS51IBMc9JWAhOSg-JOvnJJsWhvgbCwoZkaUpJ2jNWCCpRjFPQEu9GoH-bORbfV2O7b3O3WjtQ8_DA__WX_Zzrhe01HsZ04_7rT75BR7r7fmtwmU4pcmcpFBmcNjdX_-YSohCsgxJriEIq31PA79658jFjnheEqegHaTBo98F8rV5gYolf2y5jERnRYmrieNMWHmkzSlt_F2xOCXYbXf7Q65gJ9CYCA_6UlxbuXJLlCkKqr_nUegN-PYHpDvG_q4GGpvPiyuIQkO8XK9TqbV_WJWynE_q0pPwC9skMtX_D_m70ZxZOsKwBjngwE9iQrCX-at4TYtaAgzxWDlC30IZh_h5qM1uWXVJRV9Fy9kaoJo1ljwf8HqdrD7nyuvNwagqXCkqgvYL7vpymXp_oRYmoVKtx_OzZ3zxGHKgIzrYGsTDsx54XTZjPfGoz7kwyaR4wLfmq6WvhRC3N64q-OB7slR8kWZDeBDTsXwVKf4ZgguTL1b9yy79UOxBIHM_SV9WuIk9LhmWoHcEE95TzAC0094NOndhf0rPNxt55W5SDZ2WkPUwPl6a-0_ilyrPHSzB7CZyqznLAenHyK6lCBUIsDF5J-kbqu80Tx3K9kv43-0Px2BsQiBbrpI2PKkKYp1Fvf70-hCV0QOcsjIKB_2PBzMjay1MQStEEE8fDNVU9NkBqdv3YjC0W41Lp5x6d0dRywFgqpmjnmxBuG3hvVB5dHEbfieyXoth1i8IZuKstZyklQf6UC2EUXsQZp1NdaAUxHNR52veJdg9Jt6E-w0RdaKAP9DrvdM6DzmEPDt4XkrmAzC9v-yTNvnXxhtbwSQAC3NWQs3xCSKXXBl5czTZO3tsQELnH5Lu0RuTN7J3SK_Ce1dTn2NS0SYz00fP8jAGrlMBlpJTgTaBx_vXfgeRIT7lq_CjzqbvSqpPlNHeUzTFcZTOB8Bq1ZWbRlb_Y2AnDve50tH9VypxL6Cdr6-khON4nCiTcQBwPjTLuh5JTRLL0XfXClgSBYcwUfHSTHg07iIl-KSQqdirQhZjKYLBJ4Z-e9xJuQy-SwKJ4yT9rHhYloWmtz6T4jWHaZKy-NPHwr-ztilZtuml_eR5b9khRkJoA6bIGoJEHF_2ycri2vHyUNq8Ym2WimyWhzft7aTUZZKUKSy2q6RZrQEvb0Ltet32UPPJ6FMRiVl_FZJvKWPgTbjHcIChesPPeBy57hm0CDUk6etCNEZpDwfxyyZgtxTDSxagnorHBArQxgwC8pKYP4haVhEWhPa3z9CXpTuRTEAzWO1skyFi0P4ydqtt1Mac05yvZb-URbKw2q87Ji2ohFul7gOC-a3WErA4J9cayZ4A5k-OL--uXGPIAxmkElAr0FPBYLLYWPNyUXwNwkV8o8Oxh1K0zeAZV8ccypXwCsmm4x19pifHc7tjaLgV0qJa62M6S9GAhBStIxtbXz0fSfPnYAmbMjOycI9OLNqeRpZ2sEhQq3fWlydLwR2H3CtLRsD7Moh3sCJN36ocOxBWlJh4dt4rafvEOa0KiQBoG9FhqQCpu9NhI8SV95MIY9dCI07OGlQy_argVC8GkKZ7ZKtdgZ2UequKSAkRCtw7uGqC54XO1vXAPXwV3Qsx04eLil3X8D816P4dFOIzU4rK5nudgDA_H0e2Ym2Gr1cCq7U3JO7KWHXdy7Uqe09CZgzWDucjInnsaLyCDPCTarLi15uzxxgMQN2ncvj788GpE8rN807jYa-5lZsVC7liFMF-78IozCbe7VkrGw3VJhVy3_1akkwEYir1Xot0BrC40eYsUkF1Ojw4xOCboH6k-p43XTsyFdVUfT2X_GgwLii00Po4qX6R3NlLT5UpF0FiDfllh5YMl9DB5Xa0L1ZewyvglOeJuj32rOsC8wrpq_ytINATyZsJiXf-1gEQj4wG19GmlpklO5dGWMWAw8pUhKy1l-EdDXkjL7AkEMGtckt4tULlbMtfN2QYl5mvl56sURYxJ4n5_urAIeKecgplF9FKku_b7e-2VsHnLeG59dlSOIrHRLSx7Jyg7ylaWWiEBLZK1oQrchEp3qil3rekd2rBhQty8cCNM4Ez23dtlmGp9WJIk-esVp31OqQN4hnZbP3QX9jm8AeefhHUjnT9uysmYebPjflqB4lE4gFR8E5pN0Bb8E8GtTEO3hnUHljYGqzFAx25FvAuoxAJiu2wJWVVnfaxmxXgjbcx8zOvblqfGnXW0T-NHFs76ifaoG0hBUTG6KFKSGRLZQVmCW-J1-DwxZlxzUJCRdUfqU7ze7cDF6WmFUcr-bo_VfSAwIn9rvsa-LACRqFAxgU-DshrbrrJ4dv_6ng80Ita6nbgMsPxt7zAs4EBtBZP330znjj3bbowAvwBpAyXbmPhVm-hlOs-LDeIyvlt4Pw';
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
