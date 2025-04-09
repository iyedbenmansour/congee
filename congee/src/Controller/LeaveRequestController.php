<?php

namespace App\Controller;

use App\Entity\LeaveRequest;
use App\Entity\OnlineJob;

use App\Form\LeaveRequestType;
use App\Repository\LeaveRequestRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class LeaveRequestController extends AbstractController
{
    #[Route('/leave-request/create', name: 'leave_request_create', methods: ['GET', 'POST'])]
    public function create(Request $request, EntityManagerInterface $entityManager): Response
    {
        $leaveRequest = new LeaveRequest();

        $form = $this->createForm(LeaveRequestType::class, $leaveRequest);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $leaveRequest->setConfirmed(false); // Default confirmation status is false
            $entityManager->persist($leaveRequest);
            $entityManager->flush();

            $this->addFlash('success', 'Leave request created successfully!');
            return $this->redirectToRoute('employee_leave_requests', ['employeeId' => $leaveRequest->getEmployeeId()]);
        }

        return $this->render('leave_request/create.html.twig', [
            'form' => $form->createView(),
        ]);
    }

  
#[Route('/leave-requests/employee/{employeeId}', name: 'employee_leave_requests', methods: ['GET'])]
public function employeeLeaveRequests(int $employeeId, LeaveRequestRepository $leaveRequestRepository, EntityManagerInterface $entityManager): Response
{
    $leaveRequests = $leaveRequestRepository->findBy(['employeeId' => $employeeId]);
    
    // Get all online jobs for these leave requests
    $onlineJobs = [];
    if (!empty($leaveRequests)) {
        $leaveRequestIds = array_map(fn($lr) => $lr->getId(), $leaveRequests);
        $onlineJobs = $entityManager->getRepository(OnlineJob::class)
            ->findBy(['leaveRequestId' => $leaveRequestIds]);
        
        // Create a mapping of leaveRequestId => OnlineJob
        $onlineJobs = array_reduce($onlineJobs, function($carry, $oj) {
            $carry[$oj->getLeaveRequestId()] = $oj;
            return $carry;
        }, []);
    }

    return $this->render('leave_request/employee_list.html.twig', [
        'leaveRequests' => $leaveRequests,
        'onlineJobs' => $onlineJobs,
    ]);
}

    #[Route('/leave-request/edit/{id}', name: 'leave_request_edit', methods: ['GET', 'POST'])]
    public function edit(int $id, Request $request, LeaveRequestRepository $leaveRequestRepository, EntityManagerInterface $entityManager): Response
    {
        $leaveRequest = $leaveRequestRepository->find($id);

        if (!$leaveRequest) {
            throw $this->createNotFoundException('Leave request not found');
        }

        $form = $this->createForm(LeaveRequestType::class, $leaveRequest);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            $this->addFlash('success', 'Leave request updated successfully!');
            return $this->redirectToRoute('employee_leave_requests', ['employeeId' => $leaveRequest->getEmployeeId()]);
        }

        return $this->render('leave_request/edit.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route('/leave-request/delete/{id}', name: 'leave_request_delete', methods: ['POST'])]
    public function delete(int $id, LeaveRequestRepository $leaveRequestRepository, EntityManagerInterface $entityManager): Response
    {
        $leaveRequest = $leaveRequestRepository->find($id);

        if (!$leaveRequest) {
            throw $this->createNotFoundException('Leave request not found');
        }

        $employeeId = $leaveRequest->getEmployeeId();
        $entityManager->remove($leaveRequest);
        $entityManager->flush();

        $this->addFlash('success', 'Leave request deleted successfully!');
        return $this->redirectToRoute('employee_leave_requests', ['employeeId' => $employeeId]);
    }


#[Route('/leave-requests/company/{companyId}', name: 'company_leave_requests', methods: ['GET'])]
public function companyLeaveRequests(int $companyId, LeaveRequestRepository $leaveRequestRepository, EntityManagerInterface $entityManager): Response
{
    $leaveRequests = $leaveRequestRepository->findBy(['companyId' => $companyId]);
    
    // Get all online jobs for these leave requests
    $onlineJobs = [];
    if (!empty($leaveRequests)) {
        $leaveRequestIds = array_map(fn($lr) => $lr->getId(), $leaveRequests);
        $onlineJobResults = $entityManager->getRepository(OnlineJob::class)
            ->findBy(['leaveRequestId' => $leaveRequestIds]);
        
        // Create a mapping of leaveRequestId => OnlineJob
        foreach ($onlineJobResults as $onlineJob) {
            $onlineJobs[$onlineJob->getLeaveRequestId()] = $onlineJob;
        }
    }

    return $this->render('leave_request/company_list.html.twig', [
        'leaveRequests' => $leaveRequests,
        'onlineJobs' => $onlineJobs,
    ]);
}

    #[Route('/leave-request/confirm/{id}', name: 'leave_request_confirm', methods: ['POST'])]
    public function confirm(int $id, LeaveRequestRepository $leaveRequestRepository, EntityManagerInterface $entityManager): Response
    {
        $leaveRequest = $leaveRequestRepository->find($id);

        if (!$leaveRequest) {
            throw $this->createNotFoundException('Leave request not found');
        }

        $leaveRequest->setConfirmed(true);
        $entityManager->flush();

        $this->addFlash('success', 'Leave request confirmed successfully!');
        return $this->redirectToRoute('company_leave_requests', ['companyId' => $leaveRequest->getCompanyId()]);
    }
    #[Route('/leave-request/revoke/{id}', name: 'leave_request_revoke', methods: ['POST'])]
    public function revoke(int $id, LeaveRequestRepository $leaveRequestRepository, EntityManagerInterface $entityManager): Response
    {
        $leaveRequest = $leaveRequestRepository->find($id);

        if (!$leaveRequest) {
            throw $this->createNotFoundException('Leave request not found');
        }

        $leaveRequest->setConfirmed(false); // Revoke the confirmation
        $entityManager->flush();

        $this->addFlash('success', 'Leave request confirmation revoked successfully!');
        return $this->redirectToRoute('company_leave_requests', ['companyId' => $leaveRequest->getCompanyId()]);
    }

    #[Route('/company/{companyId}/leave-ranking', name: 'company_leave_ranking')]
    public function companyLeaveRanking(
        int $companyId,
        LeaveRequestRepository $leaveRequestRepository
    ): Response {
        // Get all leave requests for the company
        $leaveRequests = $leaveRequestRepository->findBy(['companyId' => $companyId]);
        
        // Count leave requests per employee
        $employeeLeaveCount = [];
        foreach ($leaveRequests as $leaveRequest) {
            $employeeId = $leaveRequest->getEmployeeId();
            if (!isset($employeeLeaveCount[$employeeId])) {
                $employeeLeaveCount[$employeeId] = 0;
            }
            $employeeLeaveCount[$employeeId]++;
        }
        
        // Sort by leave count (ascending - least to most)
        asort($employeeLeaveCount);
        
        // Prepare ranking data with comments
        $leaveRanking = [];
        $rank = 1;
        $total = count($employeeLeaveCount);
    
        foreach ($employeeLeaveCount as $employeeId => $leaveCount) {
            // Assign comments based on rank or position
            $comment = match ($rank) {
                1 => '🏆 Best Attendance',
                $total => '⚠️ Most Leaves Taken',
                default => 'Average Attendance',
            };
    
            $leaveRanking[] = [
                'rank' => $rank++,
                'employeeId' => $employeeId,
                'leaveCount' => $leaveCount,
                'comment' => $comment
            ];
        }
        
        return $this->render('leave_request/company.html.twig', [
            'companyId' => $companyId,
            'leaveRanking' => $leaveRanking
        ]);
    }
    

}