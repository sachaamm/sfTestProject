<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Psr\Log\LoggerInterface;

use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Encoder\XmlEncoder;
use Symfony\Component\Serializer\Encoder\CsvEncoder;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Serializer;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

use Symfony\Component\Validator\Constraints;

use App\Entity\Question;
use App\Entity\QuestionHistory;



use Symfony\Component\VarExporter\VarExporter;

class RestController extends AbstractController
{


    private $logger;

    public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    /**
     * @Route("/rest", name="rest")
     */
    public function index()
    {
        return $this->render('rest/index.html.twig', [
            'controller_name' => 'RestController',
        ]);
    }

    /**
     * @Route("/question", name="question_create", methods={"POST"})
     */
    public function createQuestion(Request $request, SerializerInterface $serializer, ValidatorInterface $validator) : JsonResponse
    {
        $content = $request->getContent();

        $question = $serializer->deserialize($content, Question::class, 'json');
        $question->setCreated(new \DateTime());
        $question->setUpdated(new \DateTime());

        $entityManager = $this->getDoctrine()->getManager();
        $entityManager->persist($question);
        $entityManager->flush();

        return new JsonResponse(['status' => 'Question created!'], 
        Response::HTTP_CREATED);
    }

    /**
     * @Route("/question", name="question_update", methods={"PUT"})
     */
    public function updateQuestion(Request $request, SerializerInterface $serializer){

        $content = $request->getContent();
        $decode = json_decode($content);
        
        // Find entity with title 
       $repository = $this->getDoctrine()->getRepository(Question::class);
       $question = $repository->findOneByTitle($decode->title);

        // Save current state of question into question history/ // Create question historic
        $questionHistory = new QuestionHistory();
        $questionHistory->setTitle($question->getTitle());
        $questionHistory->setStatus($question->getStatus());
        $questionHistory->setQuestion($question);
        $questionHistory->setUpdated(new \Datetime());

        // Update entity
        $question->setTitle($decode->newTitle);
        $question->setStatus($decode->newStatus);
        $question->setUpdated(new \Datetime());

        // Update and persist
        $entityManager = $this->getDoctrine()->getManager();
        $entityManager->persist($questionHistory);
        $entityManager->flush();

        return new JsonResponse(['status'=> $decode->title], 
        Response::HTTP_OK);
    }

    /**
     * @Route("/questions/export", name="questions_export", methods={"GET"})
     */
    public function export(SerializerInterface $serializer){
        
        $results = $this->getDoctrine()->getRepository(Question::class)->findAll(); 
        $exported = VarExporter::export($results);
        $data = file_put_contents('exported.php', $exported);

        return new JsonResponse(['status' => 'created'], 
        Response::HTTP_OK);

    }
}
