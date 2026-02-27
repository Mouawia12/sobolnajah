<?php

namespace App\Actions\Publication;

use App\Models\AgendaScolaire\Gallery;
use App\Models\AgendaScolaire\Publication;
use App\Services\OpenAiTranslationService;

class UpdatePublicationAction
{
    public function __construct(
        private OpenAiTranslationService $translator,
        private PublicationImageManager $imageManager
    ) {
    }

    public function execute(Publication $publication, array $data, ?int $currentSchoolId): void
    {
        if ($currentSchoolId && (int) $data['school_id2'] !== (int) $currentSchoolId) {
            abort(403, 'غير مصرح لك بتعديل منشورات مدرسة مختلفة.');
        }

        $translations = $this->translator->translatePublicationContent(
            (string) $data['titlear'],
            (string) $data['bodyar']
        );

        $publication->school_id = (int) $data['school_id2'];
        $publication->grade_id = (int) $data['grade_id2'];
        $publication->agenda_id = (int) $data['agenda_id'];
        $publication->title = $translations['title'];
        $publication->body = $translations['body'];
        $publication->save();

        $newImages = $this->imageManager->storeMany((array) ($data['img_url'] ?? []));
        if (empty($newImages)) {
            return;
        }

        $gallery = Gallery::query()->where('publication_id', $publication->id)->first();
        if (!$gallery) {
            return;
        }

        if ($gallery->img_url) {
            $oldImages = json_decode($gallery->img_url, true);
            foreach ((array) $oldImages as $image) {
                $this->imageManager->delete((string) $image);
            }
        }

        $gallery->img_url = json_encode($newImages);
        $gallery->save();
    }
}

