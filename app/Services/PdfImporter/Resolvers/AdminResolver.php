<?php

namespace App\Services\PdfImporter\Resolvers;

use Illuminate\Support\Facades\DB;

class AdminResolver
{
    /** @var array<string, int> Cache name→id */
    private array $cache = [];

    public function resolve(string $nomeNoPdf): ?int
    {
        $normalized = $this->normalize($nomeNoPdf);

        if (isset($this->cache[$normalized])) {
            return $this->cache[$normalized];
        }

        $administradoras = DB::table('administradoras')->select('id', 'nome')->get();

        foreach ($administradoras as $adm) {
            if (str_contains($this->normalize($adm->nome), $normalized)
                || str_contains($normalized, $this->normalize($adm->nome))) {
                $this->cache[$normalized] = $adm->id;
                return $adm->id;
            }
        }

        return null;
    }

    private function normalize(string $value): string
    {
        $value = mb_strtolower($value);
        $value = iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $value) ?? $value;
        return trim(preg_replace('/\s+/', ' ', $value));
    }
}
