<?php

namespace App\Services\PdfImporter\Resolvers;

use Illuminate\Support\Facades\DB;

class PlanResolver
{
    /** @var array<string, int|null> */
    private array $cache = [];

    public function resolve(string $nomeNoPdf): ?int
    {
        $normalized = $this->normalize($nomeNoPdf);

        if (array_key_exists($normalized, $this->cache)) {
            return $this->cache[$normalized];
        }

        $planos = DB::table('planos')->select('id', 'nome')->get();

        foreach ($planos as $plano) {
            if ($this->normalize($plano->nome) === $normalized) {
                $this->cache[$normalized] = $plano->id;
                return $plano->id;
            }
        }

        // Partial match
        foreach ($planos as $plano) {
            if (str_contains($this->normalize($plano->nome), $normalized)
                || str_contains($normalized, $this->normalize($plano->nome))) {
                $this->cache[$normalized] = $plano->id;
                return $plano->id;
            }
        }

        $this->cache[$normalized] = null;
        return null;
    }

    private function normalize(string $value): string
    {
        $value = mb_strtolower(trim($value));
        $value = iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $value) ?? $value;
        return trim(preg_replace('/\s+/', ' ', $value));
    }
}
