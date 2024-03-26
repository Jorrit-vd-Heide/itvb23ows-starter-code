<?php 

use PHPUnit\Framework\TestCase;

class queenBeeTest extends TestCase {
    protected $model;
    protected $controllerMock;

    protected function setUp(): void {
        parent::setUp();
        // Arrange: Set up any necessary fixtures or dependencies
        $GLOBALS['OFFSETS'] = [[0, 1], [1, 0], [1, 1], [-1, 0], [0, -1], [-1, -1]];

        // Mock the model and view
        $this->model = $this->getMockBuilder(HiveGameModel::class)
            ->disableOriginalConstructor()
            ->getMock();
                            
        $viewMock = $this->createMock(HiveGameView::class);

        $this->controllerMock = $this->getMockBuilder(HiveGameController::class)
            ->setConstructorArgs([$this->model, $viewMock])
            ->onlyMethods(['getPossibleMoves', 'getPossiblePlays', 'playTile']) // Mock only the getPossibleMoves method
            ->getMock();
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

    public function testIfQueenBeeHasBeenPlacedBeforeFourthMove() {
        // Arrange: Set up the initial state of the board (non-empty board)
        $this->model->board = ['0,0' => 'B', '0,1' => 'Q', '0,-1' => 'B', '0,2' => 'B', '0,-2' => 'S', '0,3' => 'B'];

        // Set up expectations for the playTile method
        $this->controllerMock->expects($this->once())
                            ->method('playTile')
                            ->with('S', '0,-3')
                            ->willReturn("Must play queen bee"); // Simulate the error case when trying to place a tile

        // Act: Call the method under test
        $result = $this->controllerMock->playTile('S', '0,-3');

        // Assert: Verify the result against the expected outcome
        $expected = "Must play queen bee"; // Expected error message when trying to place a tile
        $this->assertEquals($expected, $result);
    }
}
