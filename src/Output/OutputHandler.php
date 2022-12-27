<?php

namespace Minicli\Output;

use Minicli\App;
use Minicli\Output\Adapter\DefaultPrinterAdapter;
use Minicli\Output\Helper\TableHelper;
use Minicli\ServiceInterface;

class OutputHandler implements ServiceInterface
{
    /** @var PrinterAdapterInterface */
    protected $printer_adapter;

    /** @var array */
    protected $output_filters = [];

    /**
     * OutputHandler constructor.
     * @param PrinterAdapterInterface $printer
     */
    public function __construct(PrinterAdapterInterface $printer = null)
    {
        $this->printer_adapter = $printer ?? new DefaultPrinterAdapter();
    }

    /**
     * @param OutputFilterInterface $filter
     */
    public function registerFilter(OutputFilterInterface $filter): void
    {
        $this->output_filters[] = $filter;
    }

    /**
     * Removes all filters.
     */
    public function clearFilters(): void
    {
        $this->output_filters = [];
    }

    /**
     * @param App $app
     * @return bool
     */
    public function load(App $app)
    {
        return true;
    }

    /**
     * Pass content through current configured filter(s).
     * @param string $content
     * @param string $style
     * @return string
     */
    public function filterOutput($content, $style = null): string
    {
        /** @var OutputFilterInterface $filter */

        foreach ($this->output_filters as $filter) {
            $content = $filter->filter($content, $style);
        }

        return $content;
    }

    /**
     * Prints a content using configured filters
     * @param string $content
     * @param string $style
     * @return self
     */
    public function out($content, $style = "default")
    {
        $this->printer_adapter->out($this->filterOutput($content, $style));
        return $this;
    }

    /**
     * Prints content without formatting or styling
     * @param string $content
     * @return self
     */
    public function rawOutput($content)
    {
        $this->printer_adapter->out($content);
        return $this;
    }

    /**
     * Prints a new line.
     * @return self
     */
    public function newline()
    {
        $this->rawOutput("\n");
        return $this;
    }

    /**
     * Displays content using the "default" style
     * @param string $content
     * @param bool $alt Whether or not to use the inverted style ("alt")
     * @return self
     */
    public function display($content, $alt = false)
    {
        $this->newline();
        $this->out($content, $alt ? "alt" : "default");
        $this->newline();
        return $this;
    }

    /**
     * Prints content using the "error" style
     * @param string $content
     * @param bool $alt Whether or not to use the inverted style ("error_alt")
     * @return self
     */
    public function error($content, $alt = false)
    {
        $this->newline();
        $this->out($content, $alt ? "error_alt" : "error");
        $this->newline();
        return $this;
    }

    /**
     * Prints content using the "info" style
     * @param string $content
     * @param bool $alt Whether or not to use the inverted style ("info_alt")
     * @return self
     */
    public function info($content, $alt = false)
    {
        $this->newline();
        $this->out($content, $alt ? "info_alt" : "info");
        $this->newline();
        return $this;
    }

    /**
     * Prints content using the "success" style
     * @param string $content The string to print
     * @param bool $alt Whether or not to use the inverted style ("success_alt")
     * @return self
     */
    public function success($content, $alt = false)
    {
        $this->newline();
        $this->out($content, $alt ? "success_alt" : "success");
        $this->newline();
        return $this;
    }

    /**
     * Shortcut method to print tables using the TableHelper
     * @param array $table An array containing all table rows. Each row must be an array with the individual cells.
     * @return self
     */
    public function printTable(array $table)
    {
        $helper = new TableHelper($table);

        $filter = (isset($this->output_filters[0]) && $this->output_filters[0] instanceof OutputFilterInterface) ? $this->output_filters[0] : null;
        $this->newline();
        $this->rawOutput($helper->getFormattedTable($filter));
        $this->newline();
        return $this;
    }
}
