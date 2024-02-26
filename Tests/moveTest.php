<?php 

use PHPUnit\Framework\TestCase;

class moveTest extends TestCase {
    protected $model;
    protected $controllerMock;

    protected function setUp(): void {
        parent::setUp();
        // Arrange: Set up any necessary fixtures or dependencies
        // For example, you can mock the $GLOBALS['OFFSETS'] variable
        $GLOBALS['OFFSETS'] = [[0, 1], [1, 0], [1, 1], [-1, 0], [0, -1], [-1, -1]];

        // Mock the model and view
        $this->model = $this->getMockBuilder(HiveGameModel::class)
            ->disableOriginalConstructor()
            ->getMock();
                            
        $viewMock = $this->createMock(HiveGameView::class);

        $this->controllerMock = $this->getMockBuilder(HiveGameController::class)
            ->setConstructorArgs([$this->model, $viewMock])
            ->onlyMethods(['getPossibleMoves']) // Mock only the getPossibleMoves method
            ->getMock();
    }

    public function testGetPossibleMovesWithEmptyBoard() {
        // Arrange: Set up the initial state of the board (empty board)
        $this->model->board = [];

        // Set up expectations for the getPossibleMoves method
        $this->controllerMock->expects($this->once())
            ->method('getPossibleMoves')
            ->willReturn(['0,0']); // Simulate possible moves when the board is empty

        // Act: Call the method under test
        $result = $this->controllerMock->getPossibleMoves();

        // Assert: Verify the result against the expected outcome
        $expectedMoves = ['0,0']; // Expected result when the board is empty
        $this->assertEquals($expectedMoves, $result);
    }

    public function testGetPossibleMovesWithNonEmptyBoard() {
        // Arrange: Set up the initial state of the board (non-empty board)
        $this->model->board = ['0,0' => 'tile1', '1,1' => 'tile2'];

        // Set up expectations for the getPossibleMoves method
        $this->controllerMock->expects($this->once())
                             ->method('getPossibleMoves')
                             ->willReturn(['1,0', '0,1', '1,2', '-1,1', '0,-1', '-1,0']); // Simulate possible moves when the board is not empty

        // Act: Call the method under test
        $result = $this->controllerMock->getPossibleMoves();

        // Assert: Verify the result against the expected outcome
        $expectedMoves = ['1,0', '0,1', '1,2', '-1,1', '0,-1', '-1,0']; // Expected result when the board is not empty
        $this->assertEquals($expectedMoves, $result);
    }

    public function testCertainQueenBeeMove() {
        // Arrange: Set up the initial state of the board (non-empty board)
        $this->model->board = ['0,0' => 'tile1', '1,0' => 'tile2'];

        // Set up expectations for the getPossibleMoves method
        $this->controllerMock->expects($this->once())
                             ->method('getPossibleMoves')
                             ->willReturn(['0,1', '1,1', '0,-1', '1,-1', '1,0', '2,0', '-1,0', '0,0', '-1,1', '2,-1']); // Simulate possible moves when the board is not empty

        // Act: Call the method under test
        $result = $this->controllerMock->getPossibleMoves();

        // Assert: Verify the result against the expected outcome
        $expectedMoves = ['0,1', '1,1', '0,-1', '1,-1', '1,0', '2,0', '-1,0', '0,0', '-1,1', '2,-1']; // Expected result when the board is not empty
        $this->assertEquals($expectedMoves, $result);
    }
}
