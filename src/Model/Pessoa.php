<?php

namespace Uspdev\Replicado_ws\Model;

use Uspdev\Replicado\DB;

class Pessoa
{
    // O Replicado 1.28 chama trim(null) quando há e-mails sem indicador stamtr.
    public static function email(int $codpes)
    {
        $emails = DB::fetchAll(
            'SELECT codema, stamtr FROM EMAILPESSOA WHERE codpes = CONVERT(int, :codpes)',
            ['codpes' => $codpes]
        );

        foreach ($emails as $email) {
            if (trim((string) ($email['stamtr'] ?? '')) === 'S') {
                return $email['codema'];
            }
        }

        return false;
    }
}
