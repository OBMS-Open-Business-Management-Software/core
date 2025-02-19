<?php

namespace App\Http\Controllers;

use App\Models\Content\Page;
use App\Models\Content\PageVersion;
use Exception;
use Illuminate\Contracts\Support\Renderable;
use Illuminate\Support\Facades\Route;
use Symfony\Component\ErrorHandler\Error\FatalError;
use Throwable;

class PublicPageController extends Controller
{
    /**
     * Render a certain public page.
     *
     * @return Renderable
     */
    public function render(): Renderable
    {
        $page = Page::where('route', '=', '/' . Route::current()->uri())
            ->first();

        if (empty($page)) {
            $page = Page::where('route', '=', Route::current()->uri())
                ->first();
        }

        if (! empty($page)) {
            // Laravel doesn't set this magic attribute by default. Thus we need to do a self-assignment first.
            $page->latest = $page->latest;
        }

        return view('cms.page', [
            'page' => $page,
            'render' => function ($__php, $__data) {
                $obLevel = ob_get_level();
                ob_start();
                extract($__data, EXTR_SKIP);

                try {
                    eval('?' . '>' . $__php);
                } catch (Exception $e) {
                    while (ob_get_level() > $obLevel) {
                        ob_end_clean();
                    }

                    throw $e;
                } catch (Throwable $e) {
                    while (ob_get_level() > $obLevel) {
                        ob_end_clean();
                    }

                    throw new FatalError($e, $e->getCode(), $e->getTrace());
                }

                return ob_get_clean();
            }
        ]);
    }

    /**
     * Render a certain public page with a certain version.
     *
     * @param int $id
     *
     * @return Renderable|null
     */
    public function render_version(int $id): ?Renderable
    {
        $page = Page::where('route', '=', '/' . str_replace('/{id}', '', Route::current()->uri()))
            ->first();

        if (empty($page)) {
            $page = Page::where('route', '=', str_replace('/{id}', '', Route::current()->uri()))
                ->first();
        }

        $page->latest = PageVersion::find($id);

        if ($page->latest->page_id == $page->id) {
            return view('cms.page', [
                'page' => $page,
                'render' => function ($__php, $__data) {
                    $obLevel = ob_get_level();
                    ob_start();
                    extract($__data, EXTR_SKIP);

                    try {
                        eval('?' . '>' . $__php);
                    } catch (Exception $e) {
                        while (ob_get_level() > $obLevel) {
                            ob_end_clean();
                        }

                        throw $e;
                    } catch (Throwable $e) {
                        while (ob_get_level() > $obLevel) {
                            ob_end_clean();
                        }

                        throw new FatalError($e, $e->getCode(), $e->getTrace());
                    }

                    return ob_get_clean();
                }
            ]);
        }

        return null;
    }
}
