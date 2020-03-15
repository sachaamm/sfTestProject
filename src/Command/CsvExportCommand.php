<?php

// src/Command/CreateUserCommand.php
namespace App\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

use App\Repository\QuestionRepository;
use App\Entity\Question;

class CsvExportCommand extends Command
{
    // the name of the command (the part after "bin/console")
    protected static $defaultName = 'app:csv-export';

    private $questionRepository;

    public function __construct(QuestionRepository $questionRepository)
    {
        $this->questionRepository = $questionRepository;

        parent::__construct();
    }



    protected function configure()
    {
        // ...
        $this
        // the short description shown while running "php bin/console list"
        ->setDescription('Creates a csv export.')

        // the full command description shown when running the command with
        // the "--help" option
        ->setHelp('This command allows you to export questions to a csv file...')
    ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        // ...
     
        $results = $this->questionRepository->findAll();

        $handle = fopen('questions.csv', 'w') or die("Unable to open file!");

      
        $firstLine = array();
        
        $firstLineSerialized = new \StdClass;
        $firstLineSerialized->title = "Title";
        $firstLineSerialized->status = "Status";
        $firstLineSerialized->created = "Created";
        $firstLineSerialized->updated = "Updated";
        $firstLineSerialized->updated = "Answers";    
        fputcsv($handle, (array)$firstLineSerialized);  
        
        foreach($results as &$question) {

            $questionArray = array();
            $questionSerialized = new \StdClass;
            $questionSerialized->title = $question->getTitle();
            $questionSerialized->status = $question->getStatus();
            $questionSerialized->created = $question->getCreated()->format('Y-m-d H:i:s');
            $questionSerialized->updated = $question->getUpdated()->format('Y-m-d H:i:s');
            $questionSerialized->updated = count($question->getAnswers());

            fputcsv($handle, (array)$questionSerialized);      
        }

        fclose($handle);
    
        echo("CSV Export done.");

        return 0;
    }
}

?>